<?php

namespace App\Workflow;

use App\Exception\InvalidActionInputException;

/**
 * Workflow séquentiel simple : statut courant + action → nouveau statut.
 */
final class SimpleSequentialWorkflow
{
    /**
     * @param array<string, array<string, string>> $transitions
     */
    public function __construct(
        private readonly array $transitions,
    ) {
    }

    /**
     * @return list<string>
     */
    public function getAvailableActions(string $currentStatus): array
    {
        return array_keys($this->transitions[$currentStatus] ?? []);
    }

    public function canApply(string $currentStatus, string $action): bool
    {
        return isset($this->transitions[$currentStatus][$action]);
    }

    public function resolveNextStatus(string $currentStatus, string $action): string
    {
        if (!$this->canApply($currentStatus, $action)) {
            throw new InvalidActionInputException('workflow transition not allowed');
        }

        return $this->transitions[$currentStatus][$action];
    }
}
