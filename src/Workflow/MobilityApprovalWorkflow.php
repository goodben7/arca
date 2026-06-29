<?php

namespace App\Workflow;

use App\Entity\MobilityRequest;
use App\Exception\InvalidActionInputException;
use App\Model\MobilityRequestConstants;

final class MobilityApprovalWorkflow implements ApprovalWorkflowInterface
{
    public const string ACTION_SUBMIT = 'submit';
    public const string ACTION_APPROVE = 'approve';
    public const string ACTION_REJECT = 'reject';
    public const string ACTION_CANCEL = 'cancel';

    private readonly SimpleSequentialWorkflow $workflow;

    public function __construct()
    {
        $this->workflow = new SimpleSequentialWorkflow([
            MobilityRequestConstants::STATUS_DRAFT => [
                self::ACTION_SUBMIT => MobilityRequestConstants::STATUS_MANAGER_APPROVAL,
                self::ACTION_CANCEL => MobilityRequestConstants::STATUS_CANCELLED,
            ],
            MobilityRequestConstants::STATUS_MANAGER_APPROVAL => [
                self::ACTION_APPROVE => MobilityRequestConstants::STATUS_HR_APPROVAL,
                self::ACTION_REJECT => MobilityRequestConstants::STATUS_REJECTED,
            ],
            MobilityRequestConstants::STATUS_HR_APPROVAL => [
                self::ACTION_APPROVE => MobilityRequestConstants::STATUS_EXECUTIVE_APPROVAL,
                self::ACTION_REJECT => MobilityRequestConstants::STATUS_REJECTED,
            ],
            MobilityRequestConstants::STATUS_EXECUTIVE_APPROVAL => [
                self::ACTION_APPROVE => MobilityRequestConstants::STATUS_IMPLEMENTED,
                self::ACTION_REJECT => MobilityRequestConstants::STATUS_REJECTED,
            ],
        ]);
    }

    public function supports(object $subject): bool
    {
        return $subject instanceof MobilityRequest;
    }

    public function getAvailableActions(object $subject): array
    {
        if (!$this->supports($subject)) {
            return [];
        }

        /** @var MobilityRequest $subject */
        return $this->workflow->getAvailableActions((string) $subject->getStatus());
    }

    public function apply(object $subject, string $action, array $context = []): void
    {
        if (!$this->supports($subject)) {
            throw new InvalidActionInputException('mobility request approval workflow does not support this subject');
        }

        /** @var MobilityRequest $subject */
        $currentStatus = (string) $subject->getStatus();

        if (!$this->workflow->canApply($currentStatus, $action)) {
            throw new InvalidActionInputException('Action not allowed : invalid mobility request state');
        }

        $actorId = $context['actorId'] ?? 'SYSTEM';
        $now = new \DateTimeImmutable();
        $nextStatus = $this->workflow->resolveNextStatus($currentStatus, $action);

        $subject->setStatus($nextStatus);

        match ($action) {
            self::ACTION_SUBMIT => $subject
                ->setSubmittedAt($now)
                ->setSubmittedBy($actorId),
            self::ACTION_APPROVE => $this->recordApproval($subject, $currentStatus, $now, $actorId),
            self::ACTION_REJECT => $subject
                ->setRejectedAt($now)
                ->setRejectedBy($actorId)
                ->setRejectionReason($context['reason'] ?? null),
            self::ACTION_CANCEL => $subject
                ->setCancelledAt($now)
                ->setCancelledBy($actorId),
            default => null,
        };

        if (MobilityRequestConstants::STATUS_IMPLEMENTED === $nextStatus) {
            $subject
                ->setImplementedAt($now)
                ->setImplementedBy($actorId);
        }
    }

    private function recordApproval(MobilityRequest $subject, string $fromStatus, \DateTimeImmutable $now, string $actorId): void
    {
        match ($fromStatus) {
            MobilityRequestConstants::STATUS_MANAGER_APPROVAL => $subject
                ->setManagerApprovedAt($now)
                ->setManagerApprovedBy($actorId),
            MobilityRequestConstants::STATUS_HR_APPROVAL => $subject
                ->setHrApprovedAt($now)
                ->setHrApprovedBy($actorId),
            MobilityRequestConstants::STATUS_EXECUTIVE_APPROVAL => $subject
                ->setExecutiveApprovedAt($now)
                ->setExecutiveApprovedBy($actorId),
            default => null,
        };
    }
}
