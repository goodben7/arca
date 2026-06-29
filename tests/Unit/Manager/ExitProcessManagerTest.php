<?php

namespace App\Tests\Unit\Manager;

use App\Entity\Contract;
use App\Entity\Employee;
use App\Entity\ExitProcess;
use App\Entity\ExitTask;
use App\Entity\User;
use App\Event\Domain\ExitProcessStartedEvent;
use App\Exception\InvalidActionInputException;
use App\Manager\ContractManager;
use App\Manager\EmployeeBenefitManager;
use App\Manager\EmployeeManager;
use App\Manager\ExitProcessManager;
use App\Manager\UserManager;
use App\Message\Query\QueryBusInterface;
use App\Model\ContractConstants;
use App\Model\EmployeeConstants;
use App\Model\ExitProcessConstants;
use App\Model\ExitTaskConstants;
use App\Model\NewExitProcessModel;
use App\Model\StartExitProcessModel;
use App\Repository\ContractRepository;
use App\Repository\ExitProcessRepository;
use App\Repository\ExitTaskRepository;
use App\Service\ActivityEventDispatcher;
use App\Service\OffboardingChecklistProvider;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ExitProcessManagerTest extends ManagerTestCase
{
    private EntityManagerInterface&MockObject $em;
    private ExitProcessRepository&MockObject $processRepository;
    private ExitTaskRepository&MockObject $taskRepository;
    private OffboardingChecklistProvider&MockObject $checklist;
    private EventDispatcherInterface&MockObject $domainEvents;
    private ExitProcessManager $manager;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->processRepository = $this->createMock(ExitProcessRepository::class);
        $this->taskRepository = $this->createMock(ExitTaskRepository::class);
        $this->checklist = $this->createMock(OffboardingChecklistProvider::class);
        $this->domainEvents = $this->createMock(EventDispatcherInterface::class);

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn(null);

        $this->manager = new ExitProcessManager(
            $this->em,
            $this->createMock(ActivityEventDispatcher::class),
            $security,
            $this->createMock(QueryBusInterface::class),
            $this->domainEvents,
            $this->processRepository,
            $this->taskRepository,
            $this->checklist,
            $this->createMock(ContractRepository::class),
            $this->createMock(ContractManager::class),
            $this->createMock(EmployeeManager::class),
            $this->createMock(UserManager::class),
            $this->createMock(EmployeeBenefitManager::class),
        );
    }

    public function testStartDispatchesOffboardingStartedEvent(): void
    {
        $process = (new ExitProcess())
            ->setEmployee('EMTEST001')
            ->setReason(ExitProcessConstants::REASON_RESIGNATION)
            ->setDepartureDate(new \DateTimeImmutable('2026-08-01'))
            ->setStatus(ExitProcessConstants::STATUS_PENDING);
        $this->setEntityId($process, 'EPTEST001');

        $this->em->method('find')->willReturn($process);
        $this->taskRepository->method('count')->willReturn(0);
        $this->checklist->method('getDefaultItems')->willReturn([
            ['title' => 'Exit interview', 'type' => ExitTaskConstants::TYPE_EXIT_INTERVIEW, 'dueDays' => 5],
        ]);
        $this->em->expects($this->once())->method('flush');
        $this->domainEvents
            ->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(ExitProcessStartedEvent::class));

        $result = $this->manager->startFrom(new StartExitProcessModel('EPTEST001'));

        self::assertSame(ExitProcessConstants::STATUS_IN_PROGRESS, $result->getStatus());
    }

    public function testCreateRejectsNonActiveEmployee(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_TERMINATED);

        $this->em->method('find')->with(Employee::class, 'EMTEST001')->willReturn($employee);

        $this->expectException(InvalidActionInputException::class);

        $this->manager->createFrom(new NewExitProcessModel(
            'EMTEST001',
            ExitProcessConstants::REASON_RESIGNATION,
            new \DateTimeImmutable('2026-08-01'),
        ));
    }
}
