<?php

namespace App\Model;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Link;
use App\Entity\Employee;
use App\Provider\DisciplinarySummaryProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    normalizationContext: ['groups' => 'disciplinary_summary:get'],
    operations: [
        new Get(
            uriTemplate: '/employees/{employeeId}/disciplinary-summary',
            uriVariables: [
                'employeeId' => new Link(fromClass: Employee::class),
            ],
            security: 'is_granted("ROLE_DISCIPLINARY_CASE_LIST")',
            provider: DisciplinarySummaryProvider::class,
        ),
    ],
)]
class DisciplinarySummaryResult
{
    public function __construct(
        #[Groups(['disciplinary_summary:get'])]
        public readonly string $employeeId,
        #[Groups(['disciplinary_summary:get'])]
        public readonly int $appliedSanctionCount,
        #[Groups(['disciplinary_summary:get'])]
        public readonly ?int $maxSeverityLevel,
        #[Groups(['disciplinary_summary:get'])]
        public readonly ?string $lastSanctionCode,
        #[Groups(['disciplinary_summary:get'])]
        public readonly ?string $lastSanctionLabel,
        #[Groups(['disciplinary_summary:get'])]
        public readonly ?\DateTimeImmutable $lastAppliedAt,
        #[Groups(['disciplinary_summary:get'])]
        public readonly bool $hasActiveCase,
        #[Groups(['disciplinary_summary:get'])]
        public readonly bool $isRepeatOffender,
        #[Groups(['disciplinary_summary:get'])]
        public readonly bool $requiresAcknowledgement,
        #[Groups(['disciplinary_summary:get'])]
        public readonly ?int $suggestedNextSeverity,
        #[Groups(['disciplinary_summary:get'])]
        public readonly ?string $suggestedNextCode,
        #[Groups(['disciplinary_summary:get'])]
        public readonly ?string $suggestedNextLabel,
        #[Groups(['disciplinary_summary:get'])]
        public readonly array $reasons = [],
    ) {
    }
}
