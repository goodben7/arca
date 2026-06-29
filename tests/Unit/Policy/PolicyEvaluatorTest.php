<?php

namespace App\Tests\Unit\Policy;

use App\Entity\Employee;
use App\Model\EmployeeConstants;
use App\Policy\EligibilityPolicyInterface;
use App\Policy\PolicyEvaluator;
use App\Policy\PolicyResult;
use App\Tests\Unit\Manager\ManagerTestCase;

class PolicyEvaluatorTest extends ManagerTestCase
{
    public function testEvaluateReturnsEligibleWhenNoPoliciesMatch(): void
    {
        $evaluator = new PolicyEvaluator([]);

        $result = $evaluator->evaluate('PROMOTION', $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE));

        self::assertTrue($result->isEligible());
        self::assertSame([], $result->getReasons());
    }

    public function testEvaluateAggregatesBlockingReasons(): void
    {
        $policy = new class implements EligibilityPolicyInterface {
            public function supports(string $action): bool
            {
                return 'PROMOTION' === $action;
            }

            public function evaluate(Employee $employee, array $context = []): PolicyResult
            {
                return PolicyResult::notEligible(['insufficient performance']);
            }
        };

        $evaluator = new PolicyEvaluator([$policy]);

        $result = $evaluator->evaluate('PROMOTION', $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE));

        self::assertFalse($result->isEligible());
        self::assertSame(['insufficient performance'], $result->getReasons());
    }
}
