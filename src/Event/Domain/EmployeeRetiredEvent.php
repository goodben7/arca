<?php

namespace App\Event\Domain;

use App\Entity\Employee;

class EmployeeRetiredEvent extends DomainEvent
{
    public function __construct(
        private readonly Employee $employee,
        string $actorId,
        ?\DateTimeImmutable $occurredAt = null,
    ) {
        parent::__construct($actorId, $occurredAt ?? new \DateTimeImmutable());
    }

    public function getEmployee(): Employee
    {
        return $this->employee;
    }
}
