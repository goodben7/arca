<?php

namespace App\Tests\Unit\State;

use ApiPlatform\Metadata\Post;
use App\Dto\ApproveLeaveRequestDto;
use App\Dto\RejectLeaveRequestDto;
use App\Entity\LeaveRequest;
use App\Manager\LeaveRequestManager;
use App\Model\LeaveRequestConstants;
use App\State\ApproveLeaveRequestProcessor;
use App\State\RejectLeaveRequestProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LeaveRequestProcessorTest extends TestCase
{
    private LeaveRequestManager&MockObject $manager;

    protected function setUp(): void
    {
        $this->manager = $this->createMock(LeaveRequestManager::class);
    }

    public function testApproveProcessorDelegatesToManager(): void
    {
        $leaveRequest = (new LeaveRequest())
            ->setEmployee('EMTEST001')
            ->setStatus(LeaveRequestConstants::STATUS_APPROVED);

        $dto = new ApproveLeaveRequestDto();
        $dto->leaveRequestId = 'LRTEST001';

        $this->manager
            ->expects($this->once())
            ->method('approve')
            ->with('LRTEST001')
            ->willReturn($leaveRequest);

        $processor = new ApproveLeaveRequestProcessor($this->manager);
        $result = $processor->process($dto, new Post());

        self::assertSame($leaveRequest, $result);
    }

    public function testRejectProcessorDelegatesToManager(): void
    {
        $leaveRequest = (new LeaveRequest())
            ->setEmployee('EMTEST001')
            ->setStatus(LeaveRequestConstants::STATUS_REJECTED)
            ->setReason('understaffed');

        $dto = new RejectLeaveRequestDto();
        $dto->leaveRequestId = 'LRTEST001';
        $dto->raison = 'understaffed';

        $this->manager
            ->expects($this->once())
            ->method('reject')
            ->with('LRTEST001', 'understaffed')
            ->willReturn($leaveRequest);

        $processor = new RejectLeaveRequestProcessor($this->manager);
        $result = $processor->process($dto, new Post());

        self::assertSame($leaveRequest, $result);
    }
}
