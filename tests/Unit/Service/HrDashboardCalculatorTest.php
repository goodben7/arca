<?php

namespace App\Tests\Unit\Service;

use App\Entity\Employee;
use App\Entity\JobRole;
use App\Model\EmployeeConstants;
use App\Repository\JobRoleRequiredSkillRepository;
use App\Repository\SuccessionPlanRepository;
use App\Service\CriticalJobRolesProvider;
use App\Service\HrDashboard\HrDashboardCalculator;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

class HrDashboardCalculatorTest extends TestCase
{
    public function testComputeReturnsExpectedStructure(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $criticalJobRoles = $this->createMock(CriticalJobRolesProvider::class);
        $successionPlans = $this->createMock(SuccessionPlanRepository::class);
        $requiredSkills = $this->createMock(JobRoleRequiredSkillRepository::class);

        $criticalJobRoles->method('getCriticalJobRoleIds')->willReturn(['JRCFO001', 'JRACC001']);
        $successionPlans->method('countCoveredCriticalJobRoles')->willReturn(1);
        $requiredSkills->method('findByJobRole')->willReturn([]);

        $em->method('createQueryBuilder')->willReturnCallback(function () use ($em) {
            $qb = $this->createMock(QueryBuilder::class);
            $query = $this->createMock(Query::class);

            $qb->method('select')->willReturnSelf();
            $qb->method('from')->willReturnSelf();
            $qb->method('innerJoin')->willReturnSelf();
            $qb->method('andWhere')->willReturnSelf();
            $qb->method('setParameter')->willReturnSelf();
            $qb->method('getQuery')->willReturn($query);
            $query->method('getSingleScalarResult')->willReturn(10);
            $query->method('getResult')->willReturn([]);

            return $qb;
        });

        $calculator = new HrDashboardCalculator($em, $criticalJobRoles, $successionPlans, $requiredSkills);
        $result = $calculator->compute();

        self::assertSame(10, $result['headcount']);
        self::assertSame(2, $result['criticalJobRolesTotal']);
        self::assertSame(1, $result['criticalJobRolesCovered']);
        self::assertSame(50.0, $result['successionCoveragePercent']);
        self::assertArrayHasKey('computedAt', $result);
    }
}
