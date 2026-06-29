<?php

namespace App\Event\Domain;

use App\Entity\Contract;

class ContractActivatedEvent extends DomainEvent
{
    public function __construct(
        private readonly Contract $contract,
        string $actorId,
        ?\DateTimeImmutable $occurredAt = null,
    ) {
        parent::__construct($actorId, $occurredAt ?? new \DateTimeImmutable());
    }

    public function getContract(): Contract
    {
        return $this->contract;
    }
}
