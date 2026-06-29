<?php

namespace App\Workflow;

use App\Entity\LeaveRequest;
use App\Exception\InvalidActionInputException;
use App\Model\LeaveRequestConstants;

final class LeaveRequestApprovalWorkflow implements ApprovalWorkflowInterface
{
    public const string ACTION_APPROVE = 'approve';
    public const string ACTION_REJECT = 'reject';

    private readonly SimpleSequentialWorkflow $workflow;

    public function __construct()
    {
        $this->workflow = new SimpleSequentialWorkflow([
            LeaveRequestConstants::STATUS_PENDING => [
                self::ACTION_APPROVE => LeaveRequestConstants::STATUS_APPROVED,
                self::ACTION_REJECT => LeaveRequestConstants::STATUS_REJECTED,
            ],
        ]);
    }

    public function supports(object $subject): bool
    {
        return $subject instanceof LeaveRequest;
    }

    public function getAvailableActions(object $subject): array
    {
        if (!$this->supports($subject)) {
            return [];
        }

        /** @var LeaveRequest $subject */
        return $this->workflow->getAvailableActions((string) $subject->getStatus());
    }

    public function apply(object $subject, string $action, array $context = []): void
    {
        if (!$this->supports($subject)) {
            throw new InvalidActionInputException('leave request approval workflow does not support this subject');
        }

        /** @var LeaveRequest $subject */
        $currentStatus = (string) $subject->getStatus();

        if (!$this->workflow->canApply($currentStatus, $action)) {
            throw new InvalidActionInputException('Action not allowed : invalid leave request state');
        }

        $actorId = $context['actorId'] ?? 'SYSTEM';
        $subject
            ->setStatus($this->workflow->resolveNextStatus($currentStatus, $action))
            ->setApprovedBy($actorId);

        if (self::ACTION_REJECT === $action) {
            $subject->setReason($context['reason'] ?? null);
        }
    }
}
