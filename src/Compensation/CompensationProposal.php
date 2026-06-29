<?php

namespace App\Compensation;

final class CompensationProposal
{
    public function __construct(
        private readonly string $newSalary,
        private readonly string $reason,
    ) {
    }

    public function getNewSalary(): string
    {
        return $this->newSalary;
    }

    public function getReason(): string
    {
        return $this->reason;
    }
}
