<?php

namespace App\Tests\Unit\Policy;

use App\Model\EligibilityActionConstants;
use App\Model\EmployeeConstants;
use App\Policy\RetirementEligibilityPolicy;
use App\Tests\Unit\Manager\ManagerTestCase;

class RetirementEligibilityPolicyTest extends ManagerTestCase
{
    private RetirementEligibilityPolicy $policy;

    protected function setUp(): void
    {
        $this->policy = new RetirementEligibilityPolicy();
    }

    public function testSupportsRetirementAction(): void
    {
        self::assertTrue($this->policy->supports(EligibilityActionConstants::RETIREMENT));
        self::assertFalse($this->policy->supports(EligibilityActionConstants::PROMOTION));
    }

    public function testEvaluateEligibleWhenAgeAtLeast65(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);
        $employee->setBirthDate(new \DateTimeImmutable('1950-01-01'));
        $employee->setHireDate(new \DateTimeImmutable('2020-01-01'));

        $result = $this->policy->evaluate($employee, ['now' => new \DateTimeImmutable('2026-01-01')]);

        self::assertTrue($result->isEligible());
        self::assertSame([], $result->getReasons());
    }

    public function testEvaluateEligibleWhenTenureAtLeast35YearsWithoutBirthDate(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);
        $employee->setHireDate(new \DateTimeImmutable('1980-01-01'));

        $result = $this->policy->evaluate($employee, ['now' => new \DateTimeImmutable('2026-01-01')]);

        self::assertTrue($result->isEligible());
    }

    public function testEvaluateNotEligibleWhenAgeAndTenureTooLow(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);
        $employee->setBirthDate(new \DateTimeImmutable('1980-01-01'));
        $employee->setHireDate(new \DateTimeImmutable('2015-01-01'));

        $result = $this->policy->evaluate($employee, ['now' => new \DateTimeImmutable('2026-01-01')]);

        self::assertFalse($result->isEligible());
        self::assertContains('retirement requires age >= 65 years OR career >= 35 years', $result->getReasons());
    }
}
