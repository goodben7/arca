<?php

namespace App\Event\Domain;

use App\Entity\TrainingEnrollment;

class TrainingEnrollmentCertifiedEvent extends DomainEvent
{
    public function __construct(
        private readonly TrainingEnrollment $enrollment,
        string $actorId,
        ?\DateTimeImmutable $occurredAt = null,
    ) {
        parent::__construct($actorId, $occurredAt ?? new \DateTimeImmutable());
    }

    public function getEnrollment(): TrainingEnrollment
    {
        return $this->enrollment;
    }
}
