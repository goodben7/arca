<?php

namespace App\Policy;

use App\Entity\Employee;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class PolicyEvaluator
{
    /**
     * @param iterable<EligibilityPolicyInterface> $policies
     */
    public function __construct(
        #[AutowireIterator('app.eligibility_policy')]
        private readonly iterable $policies,
    ) {
    }

    public function evaluate(string $action, Employee $employee, array $context = []): PolicyResult
    {
        $reasons = [];

        foreach ($this->policies as $policy) {
            if (!$policy->supports($action)) {
                continue;
            }

            $result = $policy->evaluate($employee, $context);

            if (!$result->isEligible()) {
                $reasons = array_merge($reasons, $result->getReasons());
            }
        }

        return [] === $reasons
            ? PolicyResult::eligible()
            : PolicyResult::notEligible($reasons);
    }
}
