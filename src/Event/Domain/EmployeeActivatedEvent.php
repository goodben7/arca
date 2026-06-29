<?php

namespace App\Event\Domain;

use App\Entity\Employee;

class EmployeeActivatedEvent extends DomainEvent
{
    public function __construct(
        private readonly Employee $employee,
        string $actorId,
        private readonly ?string $previousStatus = null,
        ?\DateTimeImmutable $occurredAt = null,
    ) {
        parent::__construct($actorId, $occurredAt ?? new \DateTimeImmutable());
    }

    public function getEmployee(): Employee
    {
        return $this->employee;
    }

    public function getPreviousStatus(): ?string
    {
        return $this->previousStatus;
    }
}
