<?php

namespace App\Workflow;

interface ApprovalWorkflowInterface
{
    public function supports(object $subject): bool;

    /**
     * @return list<string>
     */
    public function getAvailableActions(object $subject): array;

    /**
     * @param array{actorId?: string, reason?: string} $context
     */
    public function apply(object $subject, string $action, array $context = []): void;
}
