<?php

namespace App\Policy;

use App\Entity\SanctionScale;
use App\Exception\InvalidActionInputException;
use App\Model\RecidivismEvaluation;
use App\Repository\DisciplinaryCaseRepository;
use App\Repository\SanctionScaleRepository;

class DisciplinaryRecidivismPolicy
{
    public function __construct(
        private DisciplinaryCaseRepository $disciplinaryCases,
        private SanctionScaleRepository $sanctionScales,
    ) {
    }

    public function evaluateForEmployee(string $employeeId, ?SanctionScale $proposed = null, bool $acknowledged = false): RecidivismEvaluation
    {
        $count = $this->disciplinaryCases->countAppliedSanctionsForEmployee($employeeId);
        $latest = $this->disciplinaryCases->findLatestAppliedForEmployee($employeeId);
        $lastScale = $latest?->getSanctionScale();
        $lastSeverity = $this->disciplinaryCases->getMaxSeverityForEmployee($employeeId);
        $suggested = null !== $lastSeverity
            ? $this->sanctionScales->findNextActiveByMinSeverity($lastSeverity)
            : null;

        $isRepeatOffender = $count >= 1;
        $proposedSeverity = $proposed?->getSeverityLevel();

        if (!$isRepeatOffender) {
            return new RecidivismEvaluation(
                false,
                true,
                false,
                $lastSeverity,
                $lastScale?->getCode(),
                $lastScale?->getLabel(),
                $proposedSeverity,
                $suggested?->getSeverityLevel(),
                $suggested?->getCode(),
                $suggested?->getLabel(),
            );
        }

        $suggestionHint = $this->formatSuggestion($suggested, $lastScale);

        if (null === $proposedSeverity) {
            $hint = $this->formatSuggestion($suggested, $lastScale);

            return new RecidivismEvaluation(
                true,
                true,
                true,
                $lastSeverity,
                $lastScale?->getCode(),
                $lastScale?->getLabel(),
                null,
                $suggested?->getSeverityLevel(),
                $suggested?->getCode(),
                $suggested?->getLabel(),
                [
                    $hint ?? sprintf(
                        'recidivism: escalate above %s or pass acknowledgeRecidivism=true to keep the same level',
                        (string) ($lastScale?->getCode() ?? $lastSeverity),
                    ),
                ],
            );
        }

        if ($proposedSeverity < (int) $lastSeverity) {
            return new RecidivismEvaluation(
                true,
                false,
                false,
                $lastSeverity,
                $lastScale?->getCode(),
                $lastScale?->getLabel(),
                $proposedSeverity,
                $suggested?->getSeverityLevel() ?? $lastSeverity,
                $suggested?->getCode() ?? $lastScale?->getCode(),
                $suggested?->getLabel() ?? $lastScale?->getLabel(),
                [
                    sprintf(
                        'recidivism: proposed severity %d is below last applied severity %d (%s)%s',
                        $proposedSeverity,
                        $lastSeverity,
                        (string) $lastScale?->getCode(),
                        null !== $suggestionHint ? '; '.$suggestionHint : '',
                    ),
                ],
            );
        }

        if ($proposedSeverity === (int) $lastSeverity) {
            $reason = null !== $suggested
                ? sprintf(
                    'recidivism: same severity %d (%s) already applied; escalate to %s (severity %d) or pass acknowledgeRecidivism=true',
                    $proposedSeverity,
                    (string) $proposed->getCode(),
                    (string) $suggested->getCode(),
                    (int) $suggested->getSeverityLevel(),
                )
                : sprintf(
                    'recidivism: employee already at maximum severity %d (%s); pass acknowledgeRecidivism=true to apply the same level again',
                    $proposedSeverity,
                    (string) $proposed->getCode(),
                );

            return new RecidivismEvaluation(
                true,
                $acknowledged,
                true,
                $lastSeverity,
                $lastScale?->getCode(),
                $lastScale?->getLabel(),
                $proposedSeverity,
                $suggested?->getSeverityLevel(),
                $suggested?->getCode(),
                $suggested?->getLabel(),
                $acknowledged ? [] : [$reason],
            );
        }

        return new RecidivismEvaluation(
            true,
            true,
            false,
            $lastSeverity,
            $lastScale?->getCode(),
            $lastScale?->getLabel(),
            $proposedSeverity,
            $suggested?->getSeverityLevel(),
            $suggested?->getCode(),
            $suggested?->getLabel(),
        );
    }

    public function assertAllowed(string $employeeId, SanctionScale $proposed, bool $acknowledged = false): RecidivismEvaluation
    {
        $evaluation = $this->evaluateForEmployee($employeeId, $proposed, $acknowledged);
        if (!$evaluation->allowed) {
            throw new InvalidActionInputException(implode('; ', $evaluation->reasons));
        }

        return $evaluation;
    }

    private function formatSuggestion(?SanctionScale $suggested, ?SanctionScale $lastScale): ?string
    {
        if (null !== $suggested) {
            return sprintf(
                'escalate to %s (severity %d)',
                (string) $suggested->getCode(),
                (int) $suggested->getSeverityLevel(),
            );
        }

        if (null !== $lastScale) {
            return null;
        }

        return null;
    }
}
