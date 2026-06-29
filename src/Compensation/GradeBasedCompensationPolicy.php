<?php

namespace App\Compensation;

use App\Entity\Employee;
use App\Service\CompensationConfigProvider;

final class GradeBasedCompensationPolicy implements CompensationPolicyInterface
{
    private readonly float $baseSalaryPerRank;

    public function __construct(CompensationConfigProvider $config)
    {
        $this->baseSalaryPerRank = $config->getBaseSalaryPerRank();
    }

    public function supports(array $context = []): bool
    {
        return true;
    }

    public function computeNewSalary(Employee $employee, string $currentSalary, array $context = []): ?CompensationProposal
    {
        $grade = $employee->getGrade();
        $rank = $grade?->getRank();

        if (null === $rank || $rank < 1) {
            return null;
        }

        $newSalary = bcmul((string) $rank, number_format($this->baseSalaryPerRank, 2, '.', ''), 2);

        if (0 === bccomp($currentSalary, $newSalary, 2)) {
            return null;
        }

        return new CompensationProposal(
            $newSalary,
            sprintf(
                'grade-based salary for %s (rank %d)',
                $grade?->getCode() ?? 'unknown',
                $rank,
            ),
        );
    }
}
