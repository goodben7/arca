<?php

namespace App\Tests\Unit\Workflow;

use App\Entity\LeaveRequest;
use App\Exception\InvalidActionInputException;
use App\Model\LeaveRequestConstants;
use App\Workflow\LeaveRequestApprovalWorkflow;
use App\Workflow\SimpleSequentialWorkflow;
use PHPUnit\Framework\TestCase;

class LeaveRequestApprovalWorkflowTest extends TestCase
{
    public function testSimpleSequentialWorkflowResolvesTransitions(): void
    {
        $workflow = new SimpleSequentialWorkflow([
            LeaveRequestConstants::STATUS_PENDING => [
                'approve' => LeaveRequestConstants::STATUS_APPROVED,
                'reject' => LeaveRequestConstants::STATUS_REJECTED,
            ],
        ]);

        self::assertSame(['approve', 'reject'], $workflow->getAvailableActions(LeaveRequestConstants::STATUS_PENDING));
        self::assertTrue($workflow->canApply(LeaveRequestConstants::STATUS_PENDING, 'approve'));
        self::assertSame(
            LeaveRequestConstants::STATUS_APPROVED,
            $workflow->resolveNextStatus(LeaveRequestConstants::STATUS_PENDING, 'approve')
        );
    }

    public function testLeaveRequestApprovalWorkflowApprovesPendingRequest(): void
    {
        $workflow = new LeaveRequestApprovalWorkflow();
        $leaveRequest = (new LeaveRequest())
            ->setEmployee('EMTEST001')
            ->setStatus(LeaveRequestConstants::STATUS_PENDING);

        $workflow->apply($leaveRequest, LeaveRequestApprovalWorkflow::ACTION_APPROVE, ['actorId' => 'USTEST001']);

        self::assertSame(LeaveRequestConstants::STATUS_APPROVED, $leaveRequest->getStatus());
        self::assertSame('USTEST001', $leaveRequest->getApprovedBy());
    }

    public function testLeaveRequestApprovalWorkflowRejectsWithReason(): void
    {
        $workflow = new LeaveRequestApprovalWorkflow();
        $leaveRequest = (new LeaveRequest())
            ->setEmployee('EMTEST001')
            ->setStatus(LeaveRequestConstants::STATUS_PENDING);

        $workflow->apply($leaveRequest, LeaveRequestApprovalWorkflow::ACTION_REJECT, [
            'actorId' => 'USTEST001',
            'reason' => 'understaffed',
        ]);

        self::assertSame(LeaveRequestConstants::STATUS_REJECTED, $leaveRequest->getStatus());
        self::assertSame('understaffed', $leaveRequest->getReason());
    }

    public function testLeaveRequestApprovalWorkflowBlocksInvalidTransition(): void
    {
        $workflow = new LeaveRequestApprovalWorkflow();
        $leaveRequest = (new LeaveRequest())
            ->setEmployee('EMTEST001')
            ->setStatus(LeaveRequestConstants::STATUS_APPROVED);

        $this->expectException(InvalidActionInputException::class);

        $workflow->apply($leaveRequest, LeaveRequestApprovalWorkflow::ACTION_APPROVE, ['actorId' => 'USTEST001']);
    }

    public function testTerminalStatusesHaveNoAvailableActions(): void
    {
        $workflow = new LeaveRequestApprovalWorkflow();

        foreach ([
            LeaveRequestConstants::STATUS_APPROVED,
            LeaveRequestConstants::STATUS_REJECTED,
            LeaveRequestConstants::STATUS_CANCELLED,
        ] as $status) {
            $leaveRequest = (new LeaveRequest())
                ->setEmployee('EMTEST001')
                ->setStatus($status);

            self::assertSame([], $workflow->getAvailableActions($leaveRequest), $status);
        }
    }

    public function testPendingStatusAllowsApproveAndReject(): void
    {
        $workflow = new LeaveRequestApprovalWorkflow();
        $leaveRequest = (new LeaveRequest())
            ->setEmployee('EMTEST001')
            ->setStatus(LeaveRequestConstants::STATUS_PENDING);

        self::assertSame(
            [
                LeaveRequestApprovalWorkflow::ACTION_APPROVE,
                LeaveRequestApprovalWorkflow::ACTION_REJECT,
            ],
            $workflow->getAvailableActions($leaveRequest)
        );
    }

    public function testWorkflowDoesNotSupportNonLeaveRequestSubject(): void
    {
        $workflow = new LeaveRequestApprovalWorkflow();

        self::assertFalse($workflow->supports(new \stdClass()));
        self::assertSame([], $workflow->getAvailableActions(new \stdClass()));
    }
}
