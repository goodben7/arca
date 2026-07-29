<?php

namespace App\Tests\Unit\Manager;

use App\Entity\Employee;
use App\Entity\Profile;
use App\Entity\User;
use App\Enum\EntityType;
use App\Event\Domain\EmployeeActivatedEvent;
use App\Event\Domain\EmployeeCreatedEvent;
use App\Event\Domain\EmployeeRetiredEvent;
use App\Event\Domain\EmployeeTerminatedEvent;
use App\Exception\InvalidActionInputException;
use App\Manager\EmployeeManager;
use App\Manager\GradeManager;
use App\Manager\JobRoleManager;
use App\Message\Command\CommandBusInterface;
use App\Message\Command\CreateUserCommand;
use App\Message\Query\QueryBusInterface;
use App\Model\ActivateEmployeeModel;
use App\Model\AssignManagerEmployeeModel;
use App\Model\DeactivateEmployeeModel;
use App\Model\EmployeeConstants;
use App\Model\NewEmployeeModel;
use App\Model\PutEmployeeOnLeaveModel;
use App\Policy\PolicyEvaluator;
use App\Model\TerminateEmployeeModel;
use App\Model\RetireEmployeeModel;
use App\Model\UserProxyIntertace;
use App\Repository\ProfileRepository;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EmployeeManagerTest extends ManagerTestCase
{
    private EntityManagerInterface&MockObject $em;
    private ActivityEventDispatcher&MockObject $eventDispatcher;
    private Security&MockObject $security;
    private ProfileRepository&MockObject $profileRepository;
    private QueryBusInterface&MockObject $queries;
    private CommandBusInterface&MockObject $bus;
    private EventDispatcherInterface&MockObject $domainEventDispatcher;
    private JobRoleManager&MockObject $jobRoles;
    private GradeManager&MockObject $grades;
    private PolicyEvaluator&MockObject $policyEvaluator;
    private EmployeeManager $manager;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->eventDispatcher = $this->createMock(ActivityEventDispatcher::class);
        $this->security = $this->createMock(Security::class);
        $this->profileRepository = $this->createMock(ProfileRepository::class);
        $this->queries = $this->createMock(QueryBusInterface::class);
        $this->bus = $this->createMock(CommandBusInterface::class);
        $this->domainEventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->jobRoles = $this->createMock(JobRoleManager::class);
        $this->grades = $this->createMock(GradeManager::class);
        $this->policyEvaluator = $this->createMock(PolicyEvaluator::class);

        $this->security->method('getUser')->willReturn(null);

        $this->manager = new EmployeeManager(
            $this->em,
            $this->eventDispatcher,
            $this->security,
            $this->profileRepository,
            $this->queries,
            $this->bus,
            $this->domainEventDispatcher,
            $this->jobRoles,
            $this->grades,
            $this->policyEvaluator,
        );
    }

    public function testCreateFromFlushesEmployeeBeforeCreatingUser(): void
    {
        $profile = (new Profile())
            ->setLabel('Employee')
            ->setPersonType(UserProxyIntertace::PERSON_EMPLOYEE)
            ->setActive(true);

        $this->profileRepository
            ->method('findOneBy')
            ->with(['personType' => UserProxyIntertace::PERSON_EMPLOYEE])
            ->willReturn($profile);

        $createdUser = (new User())->setEmail('jane.doe@example.com');
        $this->setEntityId($createdUser, 'USTEST001');

        $persistedEmployee = null;
        $flushCount = 0;

        $this->em->expects($this->once())->method('persist')->willReturnCallback(function (object $entity) use (&$persistedEmployee): void {
            if ($entity instanceof Employee) {
                $persistedEmployee = $entity;
            }
        });
        $this->em->expects($this->exactly(2))->method('flush')->willReturnCallback(function () use (&$flushCount, &$persistedEmployee): void {
            ++$flushCount;

            if (1 === $flushCount && $persistedEmployee instanceof Employee) {
                $this->setEntityId($persistedEmployee, 'EMTEST001');
            }
        });

        $this->bus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (CreateUserCommand $command): bool {
                return 'EMTEST001' === $command->holderId
                    && EntityType::EMPLOYEE === $command->holderType;
            }))
            ->willReturn($createdUser);

        $this->eventDispatcher->expects($this->once())->method('dispatch');
        $this->domainEventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(EmployeeCreatedEvent::class));

        $model = new NewEmployeeModel(
            firstName: 'Jane',
            lastName: 'Doe',
            gender: EmployeeConstants::GENDER_FEMALE,
            hireDate: new \DateTimeImmutable('2024-01-01'),
            email: 'jane.doe@example.com',
        );

        $employee = $this->manager->createFrom($model);

        self::assertSame('EMTEST001', $employee->getId());
        self::assertSame('USTEST001', $employee->getUserId());
        self::assertSame(EmployeeConstants::STATUS_INACTIVE, $employee->getStatus());
    }

    public function testActivateFromTransitionsInactiveToActive(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_INACTIVE);

        $this->em->method('find')->willReturn($employee);
        $this->em->expects($this->once())->method('flush');
        $this->domainEventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(EmployeeActivatedEvent::class));

        $result = $this->manager->activateFrom(new ActivateEmployeeModel('EMTEST001'));

        self::assertSame(EmployeeConstants::STATUS_ACTIVE, $result->getStatus());
        self::assertNotNull($result->getActivatedAt());
        self::assertSame('SYSTEM', $result->getActivatedBy());
    }

    public function testDeactivateFromTransitionsActiveToInactive(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);

        $this->em->method('find')->willReturn($employee);
        $this->em->expects($this->once())->method('flush');

        $result = $this->manager->deactivateFrom(new DeactivateEmployeeModel('EMTEST001'));

        self::assertSame(EmployeeConstants::STATUS_INACTIVE, $result->getStatus());
        self::assertNotNull($result->getDeactivatedAt());
    }

    public function testPutOnLeaveFromTransitionsActiveToOnLeave(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);

        $this->em->method('find')->willReturn($employee);
        $this->em->expects($this->once())->method('flush');

        $result = $this->manager->putOnLeaveFrom(new PutEmployeeOnLeaveModel('EMTEST001'));

        self::assertSame(EmployeeConstants::STATUS_ON_LEAVE, $result->getStatus());
        self::assertNotNull($result->getOnLeaveAt());
    }

    public function testTerminateFromSetsStatusAndDepartureDate(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);

        $this->em->method('find')->willReturn($employee);
        $this->em->expects($this->once())->method('flush');
        $this->domainEventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(EmployeeTerminatedEvent::class));

        $result = $this->manager->terminateFrom(new TerminateEmployeeModel('EMTEST001'));

        self::assertSame(EmployeeConstants::STATUS_TERMINATED, $result->getStatus());
        self::assertNotNull($result->getTerminatedAt());
        self::assertNotNull($result->getDepartureDate());
    }

    public function testRetireFromAllowsWhenAgeIsAtLeast65Years(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);
        $employee->setBirthDate((new \DateTimeImmutable())->modify('-70 years'));
        $employee->setHireDate((new \DateTimeImmutable())->modify('-10 years'));

        $this->em->method('find')->willReturn($employee);
        $this->em->expects($this->once())->method('flush');
        $this->eventDispatcher->expects($this->once())->method('dispatch');
        $this->domainEventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(EmployeeRetiredEvent::class));
        $this->policyEvaluator->method('evaluate')->willReturn(\App\Policy\PolicyResult::eligible());

        $result = $this->manager->retireFrom(new RetireEmployeeModel('EMTEST001'));

        self::assertSame(EmployeeConstants::STATUS_RETIRED, $result->getStatus());
        self::assertNotNull($result->getRetiredAt());
        self::assertSame('SYSTEM', $result->getRetiredBy());
        self::assertNotNull($result->getDepartureDate());
    }

    public function testRetireFromAllowsWhenCareerIsAtLeast35YearsEvenIfBirthDateMissing(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);
        $employee->setHireDate((new \DateTimeImmutable())->modify('-40 years'));
        // birthDate intentionally left null

        $this->em->method('find')->willReturn($employee);
        $this->em->expects($this->once())->method('flush');
        $this->eventDispatcher->expects($this->once())->method('dispatch');
        $this->domainEventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(EmployeeRetiredEvent::class));
        $this->policyEvaluator->method('evaluate')->willReturn(\App\Policy\PolicyResult::eligible());

        $result = $this->manager->retireFrom(new RetireEmployeeModel('EMTEST001'));

        self::assertSame(EmployeeConstants::STATUS_RETIRED, $result->getStatus());
        self::assertNotNull($result->getRetiredAt());
    }

    public function testRetireFromThrowsWhenNotEligibleByAgeOrTenure(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);
        $employee->setBirthDate((new \DateTimeImmutable())->modify('-50 years'));
        $employee->setHireDate((new \DateTimeImmutable())->modify('-10 years'));

        $this->em->method('find')->willReturn($employee);
        $this->em->expects($this->never())->method('flush');
        $this->policyEvaluator->method('evaluate')->willReturn(
            \App\Policy\PolicyResult::notEligible(['retirement requires age >= 65 years OR career >= 35 years'])
        );

        $this->expectException(InvalidActionInputException::class);
        $this->expectExceptionMessage('retirement requires age >= 65 years OR career >= 35 years');

        $this->manager->retireFrom(new RetireEmployeeModel('EMTEST001'));
    }

    public function testActivateFromThrowsWhenEmployeeIsTerminated(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_TERMINATED);

        $this->em->method('find')->willReturn($employee);
        $this->em->expects($this->never())->method('flush');

        $this->expectException(InvalidActionInputException::class);
        $this->expectExceptionMessage('Action not allowed : invalid employee state');

        $this->manager->activateFrom(new ActivateEmployeeModel('EMTEST001'));
    }

    public function testAssignManagerFromThrowsWhenManagerIsSameEmployee(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);

        $this->em->method('find')->willReturn($employee);
        $this->em->expects($this->never())->method('flush');

        $this->expectException(InvalidActionInputException::class);
        $this->expectExceptionMessage('Action not allowed : manager cannot be the same employee');

        $this->manager->assignManagerFrom(new AssignManagerEmployeeModel('EMTEST001', 'EMTEST001'));
    }
}
