<?php

namespace App\Manager;

use App\Entity\LeaveRequest;
use App\Entity\User;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Message\Query\GetUserDetails;
use App\Message\Query\QueryBusInterface;
use App\Model\LeaveRequestConstants;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class LeaveRequestManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security,
        private QueryBusInterface $queries,
    ) {
    }

    public function approve(string $leaveRequestId): LeaveRequest
    {
        $leaveRequest = $this->findLeaveRequest($leaveRequestId);

        if ($leaveRequest->getStatus() !== LeaveRequestConstants::STATUS_PENDING) {
            throw new InvalidActionInputException('Action not allowed : invalid leave request state');
        }

        $identifier = $this->security->getUser()?->getUserIdentifier();
        /** @var User|null $user */
        $user = $identifier ? $this->queries->ask(new GetUserDetails($identifier)) : null;

        $leaveRequest->setStatus(LeaveRequestConstants::STATUS_APPROVED);
        $leaveRequest->setApprovedBy($user ? $user->getId() : 'SYSTEM');

        $this->em->flush();

        return $leaveRequest;
    }

    private function findLeaveRequest(string $leaveRequestId): LeaveRequest
    {
        $leaveRequest = $this->em->find(LeaveRequest::class, $leaveRequestId);

        if (null === $leaveRequest) {
            throw new UnavailableDataException(sprintf('cannot find leave request with id: %s', $leaveRequestId));
        }

        return $leaveRequest;
    }

    public function reject(string $leaveRequestId, string $reason): LeaveRequest
    {
        $leaveRequest = $this->findLeaveRequest($leaveRequestId);

        if ($leaveRequest->getStatus() !== LeaveRequestConstants::STATUS_PENDING) {
            throw new InvalidActionInputException('Action not allowed : invalid leave request state');
        }

        $identifier = $this->security->getUser()?->getUserIdentifier();
        /** @var User|null $user */
        $user = $identifier ? $this->queries->ask(new GetUserDetails($identifier)) : null;

        $leaveRequest->setStatus(LeaveRequestConstants::STATUS_REJECTED);
        $leaveRequest->setApprovedBy($user ? $user->getId() : 'SYSTEM');
        $leaveRequest->setReason($reason);

        $this->em->flush();

        return $leaveRequest;
    }
}
