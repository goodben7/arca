<?php

namespace App\Manager;

use App\Entity\Department;
use App\Entity\TrainingRequest;
use App\Event\ActivityEvent;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Message\Query\GetUserDetails;
use App\Message\Query\QueryBusInterface;
use App\Model\NewTrainingRequestModel;
use App\Model\TrainingRequestConstants;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class TrainingRequestManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private ActivityEventDispatcher $eventDispatcher,
        private Security $security,
        private QueryBusInterface $queries,
    ) {
    }

    public function createFrom(NewTrainingRequestModel $model): TrainingRequest
    {
        $department = $this->findDepartment($model->department);

        $userId = $this->security->getUser()?->getUserIdentifier();
        $user = $userId ? $this->queries->ask(new GetUserDetails($userId)) : null;

        $request = new TrainingRequest();
        $request
            ->setDepartment($department->getId())
            ->setRequestedBy($user ? $user->getId() : 'SYSTEM')
            ->setTitle($model->title)
            ->setDescription($model->description)
            ->setNumberOfParticipants((int) $model->numberOfParticipants)
            ->setPriority($model->priority)
            ->setStatus(TrainingRequestConstants::STATUS_PENDING)
        ;

        $this->em->persist($request);
        $this->em->flush();

        $this->eventDispatcher->dispatch($request, ActivityEvent::ACTION_CREATE);

        return $request;
    }

    public function approve(string $trainingRequestId): TrainingRequest
    {
        $request = $this->findTrainingRequest($trainingRequestId);

        if ($request->getStatus() !== TrainingRequestConstants::STATUS_PENDING) {
            throw new InvalidActionInputException('Action not allowed : invalid training request state');
        }

        $userId = $this->security->getUser()?->getUserIdentifier();
        $user = $userId ? $this->queries->ask(new GetUserDetails($userId)) : null;

        $request->setStatus(TrainingRequestConstants::STATUS_APPROVED);
        $request->setApprovedBy($user ? $user->getId() : 'SYSTEM');
        $request->setApprovedAt(new \DateTimeImmutable());
        $request->setRejectedBy(null);
        $request->setRejectedAt(null);
        $request->setRejectionReason(null);

        $this->em->flush();

        $this->eventDispatcher->dispatch($request, ActivityEvent::ACTION_EDIT);

        return $request;
    }

    public function reject(string $trainingRequestId, ?string $reason = null): TrainingRequest
    {
        $request = $this->findTrainingRequest($trainingRequestId);

        if ($request->getStatus() !== TrainingRequestConstants::STATUS_PENDING) {
            throw new InvalidActionInputException('Action not allowed : invalid training request state');
        }

        $userId = $this->security->getUser()?->getUserIdentifier();
        $user = $userId ? $this->queries->ask(new GetUserDetails($userId)) : null;

        $request->setStatus(TrainingRequestConstants::STATUS_REJECTED);
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
            throw new UnavailableDataException(sprintf('cannot find department with id: %s', $departmentId));
        }

        return $department;
    }

    private function findTrainingRequest(?string $trainingRequestId): TrainingRequest
    {
        if (!$trainingRequestId) {
            throw new InvalidActionInputException('trainingRequestId is required');
        }

        $request = $this->em->find(TrainingRequest::class, $trainingRequestId);

        if (null === $request) {
            throw new UnavailableDataException(sprintf('cannot find training request with id: %s', $trainingRequestId));
        }

        return $request;
    }
}
