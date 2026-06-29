<?php

namespace App\Event\Domain;

use Symfony\Contracts\EventDispatcher\Event;

abstract class DomainEvent extends Event
{
    public function __construct(
        private readonly string $actorId,
        private readonly \DateTimeImmutable $occurredAt,
    ) {
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
