<?php

namespace App\Model;

class RecidivismEvaluation
{
    /**
     * @param list<string> $reasons
     */
    public function __construct(
        public readonly bool $isRepeatOffender,
        public readonly bool $allowed,
        public readonly bool $requiresAcknowledgement,
        public readonly ?int $lastSeverityLevel,
        public readonly ?string $lastSanctionCode,
        public readonly ?string $lastSanctionLabel,
        public readonly ?int $proposedSeverityLevel,
        public readonly ?int $suggestedNextSeverity,
        public readonly ?string $suggestedNextCode,
        public readonly ?string $suggestedNextLabel,
        public readonly array $reasons = [],
    ) {
    }
}
