<?php

namespace App\Policy;

use App\Entity\Employee;
use App\Model\EligibilityActionConstants;

class RetirementEligibilityPolicy implements EligibilityPolicyInterface
{
    public function supports(string $action): bool
    {
        return EligibilityActionConstants::RETIREMENT === $action;
    }

    public function evaluate(Employee $employee, array $context = []): PolicyResult
    {
        $now = $context['now'] ?? new \DateTimeImmutable();
        \assert($now instanceof \DateTimeImmutable);

        $requiredAgeMonths = 65 * 12;
        $requiredTenureMonths = 35 * 12;

        $eligibleByAge = false;
        $eligibleByTenure = false;
        $reasons = [];

        $birthDate = $employee->getBirthDate();
        if (null !== $birthDate) {
            $ageMonths = $this->diffMonths($birthDate, $now);
            if ($ageMonths >= $requiredAgeMonths) {
                $eligibleByAge = true;
            } else {
                $reasons[] = sprintf('age requires >= %d months', $requiredAgeMonths);
            }
        }

        $hireDate = $employee->getHireDate();
        if (null !== $hireDate) {
            $tenureMonths = $this->diffMonths($hireDate, $now);
            if ($tenureMonths >= $requiredTenureMonths) {
                $eligibleByTenure = true;
            } else {
                $reasons[] = sprintf('tenure requires >= %d months', $requiredTenureMonths);
            }
        } else {
            $reasons[] = 'hireDate is required to evaluate tenure';
        }

        if ($eligibleByAge || $eligibleByTenure) {
            return PolicyResult::eligible();
        }

        $reasons[] = 'retirement requires age >= 65 years OR career >= 35 years';

        return PolicyResult::notEligible($reasons);
    }

    private function diffMonths(\DateTimeInterface $from, \DateTimeImmutable $to): int
    {
        $diff = $from->diff($to);

        return ((int) $diff->format('%y')) * 12 + (int) $diff->format('%m');
    }
}
