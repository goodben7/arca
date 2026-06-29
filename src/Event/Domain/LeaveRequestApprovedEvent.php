<?php

namespace App\Event\Domain;

use App\Entity\LeaveRequest;

class LeaveRequestApprovedEvent extends DomainEvent
{
    public function __construct(
        private readonly LeaveRequest $leaveRequest,
        string $actorId,
        ?\DateTimeImmutable $occurredAt = null,
    ) {
        parent::__construct($actorId, $occurredAt ?? new \DateTimeImmutable());
    }

    public function getLeaveRequest(): LeaveRequest
    {
        return $this->leaveRequest;
    }
}
