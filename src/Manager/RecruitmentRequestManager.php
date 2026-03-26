<?php

namespace App\Manager;

use App\Entity\Department;
use App\Entity\Position;
use App\Entity\RecruitmentRequest;
use App\Event\ActivityEvent;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Manager\JobOfferManager;
use App\Message\Query\GetUserDetails;
use App\Message\Query\QueryBusInterface;
use App\Model\NewRecruitmentRequestModel;
use App\Model\NewJobOfferModel;
use App\Model\RecruitmentRequestConstants;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class RecruitmentRequestManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private ActivityEventDispatcher $eventDispatcher,
        private Security $security,
        private QueryBusInterface $queries,
        private JobOfferManager $jobOffers,
    ) {
    }

    public function createFrom(NewRecruitmentRequestModel $model): RecruitmentRequest
    {
        $userId = $this->security->getUser()?->getUserIdentifier();
        
        $user = $userId ? $this->queries->ask(new GetUserDetails($userId)) : null;

        $department = $this->findDepartment($model->department);
        $position = $this->findPosition($model->position);

        $request = new RecruitmentRequest();
        $request
            ->setDepartment($department->getId())
            ->setPosition($position->getId())
            ->setNumberOfPositions((int) $model->numberOfPositions)
            ->setJustification($model->justification)
            ->setDescription($model->description)
            ->setStatus(RecruitmentRequestConstants::STATUS_PENDING)
            ->setRequestedBy($user ? $user->getId() : 'SYSTEM')
        ;

        $this->em->persist($request);
        $this->em->flush();

        $this->eventDispatcher->dispatch($request, ActivityEvent::ACTION_CREATE);

        return $request;
    }

    public function approve(string $recruitmentRequestId): RecruitmentRequest
    {
        $request = $this->findRecruitmentRequest($recruitmentRequestId);

        if ($request->getStatus() !== RecruitmentRequestConstants::STATUS_PENDING) {
            throw new InvalidActionInputException('Action not allowed : invalid recruitment request state');
        }

        $userId = $this->security->getUser()?->getUserIdentifier();
        $user = $userId ? $this->queries->ask(new GetUserDetails($userId)) : null;

        $request->setStatus(RecruitmentRequestConstants::STATUS_APPROVED);
        $request->setApprovedBy($user ? $user->getId() : 'SYSTEM');
        $request->setApprovedAt(new \DateTimeImmutable());
        $request->setRejectedBy(null);
        $request->setRejectedAt(null);
        $request->setRejectionReason(null);

        $this->em->flush();

        $position = $this->findPosition($request->getPosition());
        $this->jobOffers->createFrom(new NewJobOfferModel(
            $position->getTitle(),
            $request->getDescription(),
            $request->getDepartment(),
            $request->getId(),
        ));

        $this->eventDispatcher->dispatch($request, ActivityEvent::ACTION_EDIT);

        return $request;
    }

    public function reject(string $recruitmentRequestId, string $reason): RecruitmentRequest
    {
        $request = $this->findRecruitmentRequest($recruitmentRequestId);

        if ($request->getStatus() !== RecruitmentRequestConstants::STATUS_PENDING) {
            throw new InvalidActionInputException('Action not allowed : invalid recruitment request state');
        }

        $userId = $this->security->getUser()?->getUserIdentifier();
        $user = $userId ? $this->queries->ask(new GetUserDetails($userId)) : null;

        $request->setStatus(RecruitmentRequestConstants::STATUS_REJECTED);
        $request->setRejectedBy($user ? $user->getId() : 'SYSTEM');
        $request->setRejectedAt(new \DateTimeImmutable());
        $request->setRejectionReason($reason);
        $request->setApprovedBy(null);
        $request->setApprovedAt(null);

        $this->em->flush();

        $this->eventDispatcher->dispatch($request, ActivityEvent::ACTION_EDIT);

        return $request;
    }

    private function findDepartment(?string $departmentId): Department
    {
        if (!$departmentId) {
            throw new InvalidActionInputException('department is required');
        }

        $department = $this->em->find(Department::class, $departmentId);

        if (null === $department) {
            throw new UnavailableDataException(\sprintf('cannot find department with id: %s', $departmentId));
        }

        return $department;
    }

    private function findPosition(?string $positionId): Position
    {
        if (!$positionId) {
            throw new InvalidActionInputException('position is required');
        }

        $position = $this->em->find(Position::class, $positionId);

        if (null === $position) {
            throw new UnavailableDataException(\sprintf('cannot find position with id: %s', $positionId));
        }

        return $position;
    }

    private function findRecruitmentRequest(string $recruitmentRequestId): RecruitmentRequest
    {
        $request = $this->em->find(RecruitmentRequest::class, $recruitmentRequestId);

        if (null === $request) {
            throw new UnavailableDataException(\sprintf('cannot find recruitment request with id: %s', $recruitmentRequestId));
        }

        return $request;
    }
}
