<?php

namespace App\Message;

class NotifyPayrollMessage
{
    public function __construct(
        private readonly string $compensationHistoryId,
        private readonly string $employeeId,
        private readonly string $oldSalary,
        private readonly string $newSalary,
        private readonly \DateTimeImmutable $effectiveDate,
        private readonly string $reason,
    ) {
    }

    public function getCompensationHistoryId(): string
    {
        return $this->compensationHistoryId;
    }

    public function getEmployeeId(): string
    {
        return $this->employeeId;
    }

    public function getOldSalary(): string
    {
        return $this->oldSalary;
    }

    public function getNewSalary(): string
    {
        return $this->newSalary;
    }

    public function getEffectiveDate(): \DateTimeImmutable
    {
        return $this->effectiveDate;
    }

    public function getReason(): string
    {
        return $this->reason;
    }
}
