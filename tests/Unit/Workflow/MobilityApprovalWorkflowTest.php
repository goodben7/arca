<?php

namespace App\Tests\Unit\Workflow;

use App\Entity\MobilityRequest;
use App\Exception\InvalidActionInputException;
use App\Model\MobilityRequestConstants;
use App\Workflow\MobilityApprovalWorkflow;
use PHPUnit\Framework\TestCase;

class MobilityApprovalWorkflowTest extends TestCase
{
    public function testSubmitFromDraftMovesToManagerApproval(): void
    {
        $workflow = new MobilityApprovalWorkflow();
        $request = (new MobilityRequest())
            ->setEmployee('EMTEST001')
            ->setStatus(MobilityRequestConstants::STATUS_DRAFT);

        $workflow->apply($request, MobilityApprovalWorkflow::ACTION_SUBMIT, ['actorId' => 'USTEST001']);

        self::assertSame(MobilityRequestConstants::STATUS_MANAGER_APPROVAL, $request->getStatus());
        self::assertSame('USTEST001', $request->getSubmittedBy());
        self::assertNotNull($request->getSubmittedAt());
    }

    public function testFullApprovalChainReachesImplemented(): void
    {
        $workflow = new MobilityApprovalWorkflow();
        $request = (new MobilityRequest())
            ->setEmployee('EMTEST001')
            ->setStatus(MobilityRequestConstants::STATUS_DRAFT);

        $workflow->apply($request, MobilityApprovalWorkflow::ACTION_SUBMIT, ['actorId' => 'US1']);
        $workflow->apply($request, MobilityApprovalWorkflow::ACTION_APPROVE, ['actorId' => 'US2']);
        $workflow->apply($request, MobilityApprovalWorkflow::ACTION_APPROVE, ['actorId' => 'US3']);
        $workflow->apply($request, MobilityApprovalWorkflow::ACTION_APPROVE, ['actorId' => 'US4']);

        self::assertSame(MobilityRequestConstants::STATUS_IMPLEMENTED, $request->getStatus());
        self::assertSame('US2', $request->getManagerApprovedBy());
        self::assertSame('US3', $request->getHrApprovedBy());
        self::assertSame('US4', $request->getExecutiveApprovedBy());
        self::assertSame('US4', $request->getImplementedBy());
    }

    public function testRejectFromHrApproval(): void
    {
        $workflow = new MobilityApprovalWorkflow();
        $request = (new MobilityRequest())
            ->setEmployee('EMTEST001')
            ->setStatus(MobilityRequestConstants::STATUS_HR_APPROVAL);

        $workflow->apply($request, MobilityApprovalWorkflow::ACTION_REJECT, [
            'actorId' => 'USTEST001',
            'reason' => 'budget freeze',
        ]);

        self::assertSame(MobilityRequestConstants::STATUS_REJECTED, $request->getStatus());
        self::assertSame('budget freeze', $request->getRejectionReason());
    }

    public function testCancelFromDraft(): void
    {
        $workflow = new MobilityApprovalWorkflow();
        $request = (new MobilityRequest())
            ->setEmployee('EMTEST001')
            ->setStatus(MobilityRequestConstants::STATUS_DRAFT);

        $workflow->apply($request, MobilityApprovalWorkflow::ACTION_CANCEL, ['actorId' => 'USTEST001']);

        self::assertSame(MobilityRequestConstants::STATUS_CANCELLED, $request->getStatus());
        self::assertSame('USTEST001', $request->getCancelledBy());
    }

    public function testBlocksInvalidTransition(): void
    {
        $workflow = new MobilityApprovalWorkflow();
        $request = (new MobilityRequest())
            ->setEmployee('EMTEST001')
            ->setStatus(MobilityRequestConstants::STATUS_IMPLEMENTED);

        $this->expectException(InvalidActionInputException::class);

        $workflow->apply($request, MobilityApprovalWorkflow::ACTION_APPROVE, ['actorId' => 'USTEST001']);
    }
}
