<?php

namespace App\Manager;

use App\Entity\LeaveRequest;
use App\Entity\User;
use App\Event\Domain\LeaveRequestApprovedEvent;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Message\Query\GetUserDetails;
use App\Message\Query\QueryBusInterface;
use App\Workflow\LeaveRequestApprovalWorkflow;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class LeaveRequestManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security,
        private QueryBusInterface $queries,
        private EventDispatcherInterface $domainEventDispatcher,
        private LeaveRequestApprovalWorkflow $approvalWorkflow,
    ) {
    }

    public function approve(string $leaveRequestId): LeaveRequest
    {
        $leaveRequest = $this->findLeaveRequest($leaveRequestId);
        $this->applyWorkflowAction($leaveRequest, LeaveRequestApprovalWorkflow::ACTION_APPROVE);

        $this->em->flush();

        $this->domainEventDispatcher->dispatch(
            new LeaveRequestApprovedEvent($leaveRequest, $this->resolveActorId())
        );

        return $leaveRequest;
    }

    public function reject(string $leaveRequestId, string $reason): LeaveRequest
    {
        $leaveRequest = $this->findLeaveRequest($leaveRequestId);
        $this->applyWorkflowAction($leaveRequest, LeaveRequestApprovalWorkflow::ACTION_REJECT, $reason);

        $this->em->flush();

        return $leaveRequest;
    }

    private function applyWorkflowAction(LeaveRequest $leaveRequest, string $action, ?string $reason = null): void
    {
        if (!$this->approvalWorkflow->supports($leaveRequest)) {
            throw new InvalidActionInputException('leave request approval workflow does not support this subject');
        }

        if (!\in_array($action, $this->approvalWorkflow->getAvailableActions($leaveRequest), true)) {
            throw new InvalidActionInputException('Action not allowed : invalid leave request state');
        }

        $context = ['actorId' => $this->resolveActorId()];
        if (null !== $reason) {
            $context['reason'] = $reason;
        }

        $this->approvalWorkflow->apply($leaveRequest, $action, $context);
    }

    private function findLeaveRequest(string $leaveRequestId): LeaveRequest
    {
        $leaveRequest = $this->em->find(LeaveRequest::class, $leaveRequestId);

        if (null === $leaveRequest) {
            throw new UnavailableDataException(sprintf('cannot find leave request with id: %s', $leaveRequestId));
        }

        return $leaveRequest;
    }

    private function resolveActorId(): string
    {
        $identifier = $this->security->getUser()?->getUserIdentifier();
        if (!$identifier) {
            return 'SYSTEM';
        }

        /** @var User|null $user */
        $user = $this->queries->ask(new GetUserDetails($identifier));

        return $user ? $user->getId() : 'SYSTEM';
    }
}
