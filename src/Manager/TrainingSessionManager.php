<?php

namespace App\Manager;

use App\Entity\TrainingRequest;
use App\Entity\TrainingSession;
use App\Event\ActivityEvent;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Message\Query\GetUserDetails;
use App\Message\Query\QueryBusInterface;
use App\Model\TrainingSessionConstants;
use App\Model\NewTrainingSessionModel;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class TrainingSessionManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private ActivityEventDispatcher $eventDispatcher,
        private Security $security,
        private QueryBusInterface $queries,
    ) {
    }

    public function createFrom(NewTrainingSessionModel $model): TrainingSession
    {
        $trainingRequest = $this->findTrainingRequest($model->trainingRequest);

        $session = new TrainingSession();
        $session
            ->setTitle($model->title)
            ->setTrainer($model->trainer)
            ->setStartDate(\DateTimeImmutable::createFromInterface($model->startDate))
            ->setEndDate(\DateTimeImmutable::createFromInterface($model->endDate))
            ->setLocation($model->location)
            ->setCapacity((int) $model->capacity)
            ->setTrainingRequest($trainingRequest->getId())
            ->setStatus(TrainingSessionConstants::STATUS_PLANNED)
        ;

        $this->em->persist($session);
        $this->em->flush();

        $this->eventDispatcher->dispatch($session, ActivityEvent::ACTION_CREATE);

        return $session;
    }

    public function start(string $trainingSessionId): TrainingSession
    {
        $session = $this->findTrainingSession($trainingSessionId);
        $this->assertActionAllowed($session, TrainingSessionConstants::ACTION_START);
        $this->applyAction($session, TrainingSessionConstants::ACTION_START);
        $this->em->flush();
        $this->eventDispatcher->dispatch($session, ActivityEvent::ACTION_EDIT);
        return $session;
    }

    public function complete(string $trainingSessionId): TrainingSession
    {
        $session = $this->findTrainingSession($trainingSessionId);
        $this->assertActionAllowed($session, TrainingSessionConstants::ACTION_COMPLETE);
        $this->applyAction($session, TrainingSessionConstants::ACTION_COMPLETE);
        $this->em->flush();
        $this->eventDispatcher->dispatch($session, ActivityEvent::ACTION_EDIT);
        return $session;
    }

    public function cancel(string $trainingSessionId, ?string $reason = null): TrainingSession
    {
        $session = $this->findTrainingSession($trainingSessionId);
        $this->assertActionAllowed($session, TrainingSessionConstants::ACTION_CANCEL);
        $this->applyAction($session, TrainingSessionConstants::ACTION_CANCEL, $reason);
        $this->em->flush();
        $this->eventDispatcher->dispatch($session, ActivityEvent::ACTION_EDIT);
        return $session;
    }

    public function setPlanned(string $trainingSessionId): TrainingSession
    {
        $session = $this->findTrainingSession($trainingSessionId);
        $this->assertActionAllowed($session, TrainingSessionConstants::ACTION_SET_PLANNED);
        $this->applyAction($session, TrainingSessionConstants::ACTION_SET_PLANNED);
        $this->em->flush();
        $this->eventDispatcher->dispatch($session, ActivityEvent::ACTION_EDIT);
        return $session;
    }

    private function findTrainingRequest(?string $trainingRequestId): TrainingRequest
    {
        if (!$trainingRequestId) {
            throw new InvalidActionInputException('trainingRequest is required');
        }

        $request = $this->em->find(TrainingRequest::class, $trainingRequestId);
        if (null === $request) {
            throw new UnavailableDataException(\sprintf('cannot find training request with id: %s', $trainingRequestId));
        }
        return $request;
    }

    private function findTrainingSession(?string $trainingSessionId): TrainingSession
    {
        if (!$trainingSessionId) {
            throw new InvalidActionInputException('trainingSessionId is required');
        }
        $session = $this->em->find(TrainingSession::class, $trainingSessionId);
        if (null === $session) {
            throw new UnavailableDataException(sprintf('cannot find training session with id: %s', $trainingSessionId));
        }
        return $session;
    }

    private function assertActionAllowed(TrainingSession $session, string $action): void
    {
        $allowed = TrainingSessionConstants::getAllowedActionsForStatus($session->getStatus());
        if (!in_array($action, $allowed, true)) {
            throw new InvalidActionInputException('Action not allowed : invalid training session state');
        }
    }

    private function applyAction(TrainingSession $session, string $action, ?string $reason = null): void
    {
        $now = new \DateTimeImmutable();
        $actorId = $this->resolveActorId();

        match ($action) {
            TrainingSessionConstants::ACTION_START => $session
                ->setStatus(TrainingSessionConstants::STATUS_ONGOING)
                ->setStartedAt($now)
                ->setStartedBy($actorId)
                ->setCompletedAt(null)
                ->setCompletedBy(null)
                ->setCancelledAt(null)
                ->setCancelledBy(null),
            TrainingSessionConstants::ACTION_COMPLETE => $session
                ->setStatus(TrainingSessionConstants::STATUS_COMPLETED)
                ->setCompletedAt($now)
                ->setCompletedBy($actorId),
            TrainingSessionConstants::ACTION_CANCEL => $session
                ->setStatus(TrainingSessionConstants::STATUS_CANCELLED)
                ->setCancelledAt($now)
                ->setCancelledBy($actorId),
            TrainingSessionConstants::ACTION_SET_PLANNED => $session
                ->setStatus(TrainingSessionConstants::STATUS_PLANNED)
                ->setStartedAt(null)
                ->setStartedBy(null)
                ->setCompletedAt(null)
                ->setCompletedBy(null)
                ->setCancelledAt(null)
                ->setCancelledBy(null),
            default => throw new InvalidActionInputException('Action not allowed : unknown action'),
        };
    }

    private function resolveActorId(): string
    {
        $identifier = $this->security->getUser()?->getUserIdentifier();
        if (!$identifier) {
            return 'SYSTEM';
        }
        $user = $this->queries->ask(new GetUserDetails($identifier));
        return $user ? $user->getId() : 'SYSTEM';
    }
}
