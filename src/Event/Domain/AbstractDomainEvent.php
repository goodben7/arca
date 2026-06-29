<?php

namespace App\Event\Domain;

use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractDomainEvent extends Event
{
    private readonly \DateTimeImmutable $occurredAt;

    public function __construct(
        private readonly string $actorId,
        ?\DateTimeImmutable $occurredAt = null,
    ) {
        $this->occurredAt = $occurredAt ?? new \DateTimeImmutable();
    }

    public function getActorId(): string
    {
        return $this->actorId;
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
