<?php

namespace App\Event\Domain;

use App\Entity\EmployeeSkill;

class EmployeeSkillLevelUpgradedEvent extends DomainEvent
{
    public function __construct(
        private readonly EmployeeSkill $employeeSkill,
        private readonly string $previousLevel,
        string $actorId,
        ?\DateTimeImmutable $occurredAt = null,
    ) {
        parent::__construct($actorId, $occurredAt ?? new \DateTimeImmutable());
    }

    public function getEmployeeSkill(): EmployeeSkill
    {
        return $this->employeeSkill;
    }

    public function getPreviousLevel(): string
    {
        return $this->previousLevel;
    }
}
