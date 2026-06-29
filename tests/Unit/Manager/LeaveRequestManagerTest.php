<?php

namespace App\Tests\Unit\Manager;

use App\Entity\LeaveRequest;
use App\Entity\User;
use App\Event\Domain\LeaveRequestApprovedEvent;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Manager\LeaveRequestManager;
use App\Message\Query\QueryBusInterface;
use App\Model\LeaveRequestConstants;
use App\Workflow\LeaveRequestApprovalWorkflow;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class LeaveRequestManagerTest extends ManagerTestCase
{
    private EntityManagerInterface&MockObject $em;
    private Security&MockObject $security;
    private QueryBusInterface&MockObject $queries;
    private EventDispatcherInterface&MockObject $domainEventDispatcher;
    private LeaveRequestManager $manager;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->security = $this->createMock(Security::class);
        $this->queries = $this->createMock(QueryBusInterface::class);
        $this->domainEventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->security->method('getUser')->willReturn(null);

        $this->manager = new LeaveRequestManager(
            $this->em,
            $this->security,
            $this->queries,
            $this->domainEventDispatcher,
            new LeaveRequestApprovalWorkflow(),
        );
    }

    public function testApproveTransitionsPendingToApproved(): void
    {
        $leaveRequest = $this->createLeaveRequest('LRTEST001');

        $this->em->method('find')->with(LeaveRequest::class, 'LRTEST001')->willReturn($leaveRequest);
        $this->em->expects($this->once())->method('flush');
        $this->domainEventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(LeaveRequestApprovedEvent::class));

        $result = $this->manager->approve('LRTEST001');

        self::assertSame(LeaveRequestConstants::STATUS_APPROVED, $result->getStatus());
        self::assertSame('SYSTEM', $result->getApprovedBy());
    }

    public function testApproveUsesAuthenticatedUserAsActor(): void
    {
        $leaveRequest = $this->createLeaveRequest('LRTEST001');
        $securityUser = $this->createMock(UserInterface::class);
        $securityUser->method('getUserIdentifier')->willReturn('admin@arca.test');

        $user = (new User())->setEmail('admin@arca.test');
        $this->setEntityId($user, 'USTEST001');

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($securityUser);

        $queries = $this->createMock(QueryBusInterface::class);
        $queries->method('ask')->willReturn($user);

        $manager = new LeaveRequestManager(
            $this->em,
            $security,
            $queries,
            $this->domainEventDispatcher,
            new LeaveRequestApprovalWorkflow(),
        );

        $this->em->method('find')->willReturn($leaveRequest);
        $this->em->expects($this->once())->method('flush');
        $this->domainEventDispatcher->expects($this->once())->method('dispatch');

        $result = $manager->approve('LRTEST001');

        self::assertSame('USTEST001', $result->getApprovedBy());
    }

    public function testRejectTransitionsPendingToRejectedWithReason(): void
    {
        $leaveRequest = $this->createLeaveRequest('LRTEST001');

        $this->em->method('find')->with(LeaveRequest::class, 'LRTEST001')->willReturn($leaveRequest);
        $this->em->expects($this->once())->method('flush');
        $this->domainEventDispatcher->expects($this->never())->method('dispatch');

        $result = $this->manager->reject('LRTEST001', 'understaffed period');

        self::assertSame(LeaveRequestConstants::STATUS_REJECTED, $result->getStatus());
        self::assertSame('understaffed period', $result->getReason());
        self::assertSame('SYSTEM', $result->getApprovedBy());
    }

    public function testApproveThrowsWhenLeaveRequestNotFound(): void
    {
        $this->em->method('find')->willReturn(null);
        $this->em->expects($this->never())->method('flush');

        $this->expectException(UnavailableDataException::class);
        $this->expectExceptionMessage('cannot find leave request with id: LRMISSING');

        $this->manager->approve('LRMISSING');
    }

    public function testRejectThrowsWhenLeaveRequestNotFound(): void
    {
        $this->em->method('find')->willReturn(null);
        $this->em->expects($this->never())->method('flush');

        $this->expectException(UnavailableDataException::class);
        $this->expectExceptionMessage('cannot find leave request with id: LRMISSING');

        $this->manager->reject('LRMISSING', 'no longer needed');
    }

    public function testApproveThrowsWhenAlreadyApproved(): void
    {
        $leaveRequest = $this->createLeaveRequest('LRTEST001', LeaveRequestConstants::STATUS_APPROVED);

        $this->em->method('find')->willReturn($leaveRequest);
        $this->em->expects($this->never())->method('flush');
        $this->domainEventDispatcher->expects($this->never())->method('dispatch');

        $this->expectException(InvalidActionInputException::class);
        $this->expectExceptionMessage('Action not allowed : invalid leave request state');

        $this->manager->approve('LRTEST001');
    }

    public function testApproveThrowsWhenAlreadyRejected(): void
    {
        $leaveRequest = $this->createLeaveRequest('LRTEST001', LeaveRequestConstants::STATUS_REJECTED);

        $this->em->method('find')->willReturn($leaveRequest);
        $this->em->expects($this->never())->method('flush');

        $this->expectException(InvalidActionInputException::class);
        $this->expectExceptionMessage('Action not allowed : invalid leave request state');

        $this->manager->approve('LRTEST001');
    }

    public function testApproveThrowsWhenCancelled(): void
    {
        $leaveRequest = $this->createLeaveRequest('LRTEST001', LeaveRequestConstants::STATUS_CANCELLED);

        $this->em->method('find')->willReturn($leaveRequest);
        $this->em->expects($this->never())->method('flush');

        $this->expectException(InvalidActionInputException::class);
        $this->expectExceptionMessage('Action not allowed : invalid leave request state');

        $this->manager->approve('LRTEST001');
    }

    public function testRejectThrowsWhenAlreadyApproved(): void
    {
        $leaveRequest = $this->createLeaveRequest('LRTEST001', LeaveRequestConstants::STATUS_APPROVED);

        $this->em->method('find')->willReturn($leaveRequest);
        $this->em->expects($this->never())->method('flush');

        $this->expectException(InvalidActionInputException::class);
        $this->expectExceptionMessage('Action not allowed : invalid leave request state');

        $this->manager->reject('LRTEST001', 'too late');
    }

    public function testRejectThrowsWhenAlreadyRejected(): void
    {
        $leaveRequest = $this->createLeaveRequest('LRTEST001', LeaveRequestConstants::STATUS_REJECTED);

        $this->em->method('find')->willReturn($leaveRequest);
        $this->em->expects($this->never())->method('flush');

        $this->expectException(InvalidActionInputException::class);
        $this->expectExceptionMessage('Action not allowed : invalid leave request state');

        $this->manager->reject('LRTEST001', 'duplicate rejection');
    }

    public function testRejectThrowsWhenCancelled(): void
    {
        $leaveRequest = $this->createLeaveRequest('LRTEST001', LeaveRequestConstants::STATUS_CANCELLED);

        $this->em->method('find')->willReturn($leaveRequest);
        $this->em->expects($this->never())->method('flush');

        $this->expectException(InvalidActionInputException::class);
        $this->expectExceptionMessage('Action not allowed : invalid leave request state');

        $this->manager->reject('LRTEST001', 'cancelled request');
    }
}
