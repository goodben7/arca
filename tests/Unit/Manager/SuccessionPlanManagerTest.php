<?php

namespace App\Tests\Unit\Manager;

use App\Entity\JobRole;
use App\Exception\InvalidActionInputException;
use App\Manager\JobRoleManager;
use App\Manager\SuccessionPlanManager;
use App\Model\EmployeeConstants;
use App\Model\NewSuccessionPlanModel;
use App\Model\SuccessionPlanConstants;
use App\Repository\SuccessionPlanRepository;
use App\Service\ActivityEventDispatcher;
use App\Service\CriticalJobRolesProvider;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

class SuccessionPlanManagerTest extends ManagerTestCase
{
    private EntityManagerInterface&MockObject $em;
    private JobRoleManager&MockObject $jobRoles;
    private SuccessionPlanRepository&MockObject $successionPlans;
    private CriticalJobRolesProvider&MockObject $criticalJobRoles;
    private SuccessionPlanManager $manager;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->jobRoles = $this->createMock(JobRoleManager::class);
        $this->successionPlans = $this->createMock(SuccessionPlanRepository::class);
        $this->criticalJobRoles = $this->createMock(CriticalJobRolesProvider::class);

        $this->manager = new SuccessionPlanManager(
            $this->em,
            $this->createMock(ActivityEventDispatcher::class),
            $this->jobRoles,
            $this->successionPlans,
            $this->criticalJobRoles,
        );
    }

    public function testCreateFromAssignsActiveSuccessionPlan(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);
        $role = (new JobRole())->setCode('CFO')->setTitle('Directeur financier');
        $this->setEntityId($role, 'JRTEST001');

        $this->jobRoles->method('find')->with('JRTEST001')->willReturn($role);
        $this->criticalJobRoles->method('getCriticalJobRoleIds')->willReturn(['JRTEST001']);
        $this->em->method('find')->with(\App\Entity\Employee::class, 'EMTEST001')->willReturn($employee);
        $this->successionPlans->method('findActiveByCriticalJobRoleAndCandidate')->willReturn(null);
        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $plan = $this->manager->createFrom(new NewSuccessionPlanModel(
            'JRTEST001',
            'EMTEST001',
            SuccessionPlanConstants::READINESS_WITHIN_1_YEAR,
            'smoke succession',
        ));

        self::assertSame(SuccessionPlanConstants::STATUS_ACTIVE, $plan->getStatus());
        self::assertSame('EMTEST001', $plan->getCandidate());
        self::assertSame(SuccessionPlanConstants::READINESS_WITHIN_1_YEAR, $plan->getReadinessLevel());
    }

    public function testCreateFromRejectsNonCriticalJobRole(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);
        $role = (new JobRole())->setCode('ACC-JUNIOR')->setTitle('Junior');
        $this->setEntityId($role, 'JRTEST002');

        $this->jobRoles->method('find')->willReturn($role);
        $this->criticalJobRoles->method('getCriticalJobRoleIds')->willReturn(['JRTEST001']);
        $this->em->method('find')->willReturn($employee);

        $this->expectException(InvalidActionInputException::class);

        $this->manager->createFrom(new NewSuccessionPlanModel(
            'JRTEST002',
            'EMTEST001',
            SuccessionPlanConstants::READINESS_READY_NOW,
        ));
    }
}
