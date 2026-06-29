<?php

namespace App\Event\Domain;

use App\Entity\MobilityRequest;

class MobilityImplementedEvent extends DomainEvent
{
    public function __construct(
        private readonly MobilityRequest $mobilityRequest,
        string $actorId,
        ?\DateTimeImmutable $occurredAt = null,
    ) {
        parent::__construct($actorId, $occurredAt ?? new \DateTimeImmutable());
    }

    public function getMobilityRequest(): MobilityRequest
    {
        return $this->mobilityRequest;
    }
}
