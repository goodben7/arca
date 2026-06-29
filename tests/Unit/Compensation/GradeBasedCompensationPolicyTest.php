<?php

namespace App\Tests\Unit\Compensation;

use App\Compensation\GradeBasedCompensationPolicy;
use App\Model\EmployeeConstants;
use App\Service\CompensationConfigProvider;
use App\Tests\Unit\Manager\ManagerTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class GradeBasedCompensationPolicyTest extends ManagerTestCase
{
    private CompensationConfigProvider&MockObject $config;
    private GradeBasedCompensationPolicy $policy;

    protected function setUp(): void
    {
        $this->config = $this->createMock(CompensationConfigProvider::class);
        $this->config->method('getBaseSalaryPerRank')->willReturn(12000.0);
        $this->policy = new GradeBasedCompensationPolicy($this->config);
    }

    public function testComputesSalaryFromGradeRank(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);
        $employee->setGrade($this->createGrade('GRTEST004', 'G4', 4));

        $proposal = $this->policy->computeNewSalary($employee, '30000.00');

        self::assertNotNull($proposal);
        self::assertSame('48000.00', $proposal->getNewSalary());
        self::assertStringContainsString('G4', $proposal->getReason());
    }

    public function testReturnsNullWhenSalaryUnchanged(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);
        $employee->setGrade($this->createGrade('GRTEST004', 'G4', 4));

        self::assertNull($this->policy->computeNewSalary($employee, '48000.00'));
    }

    public function testReturnsNullWhenEmployeeHasNoGrade(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);

        self::assertNull($this->policy->computeNewSalary($employee, '30000.00'));
    }
}
