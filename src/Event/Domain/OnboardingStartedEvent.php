<?php

namespace App\Event\Domain;

use App\Entity\OnboardingProcess;

class OnboardingStartedEvent extends DomainEvent
{
    public function __construct(
        private readonly OnboardingProcess $process,
        string $actorId,
        ?\DateTimeImmutable $occurredAt = null,
    ) {
        parent::__construct($actorId, $occurredAt ?? new \DateTimeImmutable());
    }

    public function getProcess(): OnboardingProcess
    {
        return $this->process;
    }
}
