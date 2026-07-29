<?php

namespace App\Event\Domain;

use App\Entity\DisciplinaryCase;

class DisciplinaryCaseOpenedEvent extends DomainEvent
{
    public function __construct(
        private readonly DisciplinaryCase $case,
        string $actorId,
        ?\DateTimeImmutable $occurredAt = null,
    ) {
        parent::__construct($actorId, $occurredAt ?? new \DateTimeImmutable());
    }

    public function getCase(): DisciplinaryCase
    {
        return $this->case;
    }
}
