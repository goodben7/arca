<?php

namespace App\Compensation;

use App\Entity\Employee;

interface CompensationPolicyInterface
{
    public function supports(array $context = []): bool;

    public function computeNewSalary(Employee $employee, string $currentSalary, array $context = []): ?CompensationProposal;
}
