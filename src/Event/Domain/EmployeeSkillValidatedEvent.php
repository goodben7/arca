<?php

namespace App\Event\Domain;

use App\Entity\EmployeeSkill;

class EmployeeSkillValidatedEvent extends DomainEvent
{
    public function __construct(
        private readonly EmployeeSkill $employeeSkill,
        string $actorId,
        ?\DateTimeImmutable $occurredAt = null,
    ) {
        parent::__construct($actorId, $occurredAt ?? new \DateTimeImmutable());
    }

    public function getEmployeeSkill(): EmployeeSkill
    {
        return $this->employeeSkill;
    }
}
