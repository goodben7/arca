<?php

namespace App\Event\Domain;

use App\Entity\ExitProcess;

class ExitProcessStartedEvent extends DomainEvent
{
    public function __construct(
        private readonly ExitProcess $process,
        string $actorId,
        ?\DateTimeImmutable $occurredAt = null,
    ) {
        parent::__construct($actorId, $occurredAt ?? new \DateTimeImmutable());
    }

    public function getProcess(): ExitProcess
    {
        return $this->process;
    }
}
