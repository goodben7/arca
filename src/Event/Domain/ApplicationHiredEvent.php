<?php

namespace App\Event\Domain;

use App\Entity\Application;
use App\Entity\Employee;

class ApplicationHiredEvent extends DomainEvent
{
    public function __construct(
        private readonly Application $application,
        private readonly Employee $employee,
        string $actorId,
        ?\DateTimeImmutable $occurredAt = null,
    ) {
        parent::__construct($actorId, $occurredAt ?? new \DateTimeImmutable());
    }

    public function getApplication(): Application
    {
        return $this->application;
    }

    public function getEmployee(): Employee
    {
        return $this->employee;
    }
}
