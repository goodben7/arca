<?php

namespace App\Tests\Unit\Manager;

use App\Entity\Employee;
use App\Entity\EmployeeSkill;
use App\Entity\Skill;
use App\Entity\SkillCategory;
use App\Event\Domain\EmployeeSkillLevelUpgradedEvent;
use App\Event\Domain\EmployeeSkillValidatedEvent;
use App\Exception\InvalidActionInputException;
use App\Manager\EmployeeSkillManager;
use App\Manager\SkillManager;
use App\Model\EmployeeConstants;
use App\Model\NewEmployeeSkillModel;
use App\Model\SkillConstants;
use App\Model\ValidateEmployeeSkillModel;
use App\Repository\EmployeeSkillRepository;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use App\Message\Query\QueryBusInterface;

class EmployeeSkillManagerTest extends ManagerTestCase
{
    private EntityManagerInterface&MockObject $em;
    private ActivityEventDispatcher&MockObject $eventDispatcher;
    private Security&MockObject $security;
    private QueryBusInterface&MockObject $queries;
    private EventDispatcherInterface&MockObject $domainEventDispatcher;
    private EmployeeSkillRepository&MockObject $repository;
    private SkillManager&MockObject $skills;
    private EmployeeSkillManager $manager;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->eventDispatcher = $this->createMock(ActivityEventDispatcher::class);
        $this->security = $this->createMock(Security::class);
        $this->queries = $this->createMock(QueryBusInterface::class);
        $this->domainEventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->repository = $this->createMock(EmployeeSkillRepository::class);
        $this->skills = $this->createMock(SkillManager::class);

        $this->security->method('getUser')->willReturn(null);

        $this->manager = new EmployeeSkillManager(
            $this->em,
            $this->eventDispatcher,
            $this->security,
            $this->queries,
            $this->domainEventDispatcher,
            $this->repository,
            $this->skills,
        );
    }

    public function testAssignFromCreatesEmployeeSkill(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);
        $skill = $this->createCatalogSkill('SKTEST001', 'EXCEL');

        $this->em->method('find')->with(Employee::class, 'EMTEST001')->willReturn($employee);
        $this->skills->method('find')->with('SKTEST001')->willReturn($skill);
        $this->repository->method('findOneByEmployeeAndSkill')->willReturn(null);
        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $result = $this->manager->assignFrom(new NewEmployeeSkillModel(
            'EMTEST001',
            'SKTEST001',
            SkillConstants::LEVEL_INTERMEDIATE,
        ));

        self::assertSame('EMTEST001', $result->getEmployee());
        self::assertSame($skill, $result->getSkill());
        self::assertSame(SkillConstants::LEVEL_INTERMEDIATE, $result->getLevel());
    }

    public function testAssignFromRejectsDuplicateAssignment(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);
        $skill = $this->createCatalogSkill('SKTEST001', 'EXCEL');
        $existing = (new EmployeeSkill())->setEmployee('EMTEST001')->setSkill($skill);

        $this->em->method('find')->willReturn($employee);
        $this->skills->method('find')->willReturn($skill);
        $this->repository->method('findOneByEmployeeAndSkill')->willReturn($existing);

        $this->expectException(InvalidActionInputException::class);
        $this->manager->assignFrom(new NewEmployeeSkillModel('EMTEST001', 'SKTEST001', SkillConstants::LEVEL_BEGINNER));
    }

    public function testValidateFromDispatchesValidatedEvent(): void
    {
        $skill = $this->createCatalogSkill('SKTEST001', 'EXCEL');
        $employeeSkill = (new EmployeeSkill())
            ->setEmployee('EMTEST001')
            ->setSkill($skill)
            ->setLevel(SkillConstants::LEVEL_BEGINNER);
        $this->setEntityId($employeeSkill, 'ESTEST001');

        $this->em->method('find')->with(EmployeeSkill::class, 'ESTEST001')->willReturn($employeeSkill);
        $this->em->expects($this->once())->method('flush');
        $this->domainEventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(EmployeeSkillValidatedEvent::class));

        $result = $this->manager->validateFrom(new ValidateEmployeeSkillModel('ESTEST001'));

        self::assertNotNull($result->getValidatedAt());
    }

    public function testApplyUpdateDispatchesLevelUpgradedEvent(): void
    {
        $skill = $this->createCatalogSkill('SKTEST001', 'EXCEL');
        $employeeSkill = (new EmployeeSkill())
            ->setEmployee('EMTEST001')
            ->setSkill($skill)
            ->setLevel(SkillConstants::LEVEL_ADVANCED);
        $this->setEntityId($employeeSkill, 'ESTEST001');

        $unitOfWork = $this->createMock(UnitOfWork::class);
        $unitOfWork->method('getOriginalEntityData')->willReturn([
            'level' => SkillConstants::LEVEL_BEGINNER,
            'validatedAt' => null,
        ]);

        $this->em->method('getUnitOfWork')->willReturn($unitOfWork);
        $this->em->expects($this->once())->method('flush');
        $this->domainEventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(EmployeeSkillLevelUpgradedEvent::class));

        $this->manager->applyUpdate($employeeSkill);
    }

    private function createCatalogSkill(string $id, string $code): Skill
    {
        $category = (new SkillCategory())->setCode('FIN')->setName('Finance');
        $this->setEntityId($category, 'SKCTEST001');

        $skill = (new Skill())
            ->setCode($code)
            ->setName('Excel')
            ->setCategory($category);
        $this->setEntityId($skill, $id);

        return $skill;
    }
}
