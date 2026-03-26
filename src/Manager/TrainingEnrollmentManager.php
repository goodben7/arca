<?php

namespace App\Manager;

use App\Entity\Employee;
use App\Entity\TrainingEnrollment;
use App\Entity\TrainingSession;
use App\Event\ActivityEvent;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Message\Query\GetUserDetails;
use App\Message\Query\QueryBusInterface;
use App\Model\NewTrainingEnrollmentModel;
use App\Model\TrainingEnrollmentConstants;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class TrainingEnrollmentManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private ActivityEventDispatcher $eventDispatcher,
        private Security $security,
        private QueryBusInterface $queries,
    ) {
    }

    public function createFrom(NewTrainingEnrollmentModel $model): TrainingEnrollment
    {
        $employee = $this->findEmployee($model->employee);
        $session = $this->findTrainingSession($model->trainingSession);

        $enrollment = new TrainingEnrollment();
        $enrollment
            ->setEmployee($employee->getId())
            ->setTrainingSession($session->getId())
            ->setStatus(TrainingEnrollmentConstants::STATUS_ENROLLED)
            ->setEnrolledAt(new \DateTimeImmutable())
            ->setEnrolledBy($this->resolveActorId())
        ;

        $this->em->persist($enrollment);
        $this->em->flush();

        $this->eventDispatcher->dispatch($enrollment, ActivityEvent::ACTION_CREATE);

        return $enrollment;
    }

    public function complete(string $trainingEnrollmentId): TrainingEnrollment
    {
        $enrollment = $this->findTrainingEnrollment($trainingEnrollmentId);
        $this->assertActionAllowed($enrollment, TrainingEnrollmentConstants::ACTION_COMPLETE);
        $this->applyAction($enrollment, TrainingEnrollmentConstants::ACTION_COMPLETE);
        $this->em->flush();
        $this->eventDispatcher->dispatch($enrollment, ActivityEvent::ACTION_EDIT);
        return $enrollment;
    }

    public function markAbsent(string $trainingEnrollmentId): TrainingEnrollment
    {
        $enrollment = $this->findTrainingEnrollment($trainingEnrollmentId);
        $this->assertActionAllowed($enrollment, TrainingEnrollmentConstants::ACTION_MARK_ABSENT);
        $this->applyAction($enrollment, TrainingEnrollmentConstants::ACTION_MARK_ABSENT);
        $this->em->flush();
        $this->eventDispatcher->dispatch($enrollment, ActivityEvent::ACTION_EDIT);
        return $enrollment;
    }

    public function setEnrolled(string $trainingEnrollmentId): TrainingEnrollment
    {
        $enrollment = $this->findTrainingEnrollment($trainingEnrollmentId);
        $this->assertActionAllowed($enrollment, TrainingEnrollmentConstants::ACTION_SET_ENROLLED);
        $this->applyAction($enrollment, TrainingEnrollmentConstants::ACTION_SET_ENROLLED);
        $this->em->flush();
        $this->eventDispatcher->dispatch($enrollment, ActivityEvent::ACTION_EDIT);
        return $enrollment;
    }

    private function findEmployee(?string $employeeId): Employee
    {
        if (!$employeeId) {
            throw new InvalidActionInputException('employee is required');
        }
        $employee = $this->em->find(Employee::class, $employeeId);
        if (null === $employee) {
            throw new UnavailableDataException(sprintf('cannot find employee with id: %s', $employeeId));
        }
        return $employee;
    }

    private function findTrainingSession(?string $sessionId): TrainingSession
    {
        if (!$sessionId) {
            throw new InvalidActionInputException('trainingSession is required');
        }
        $session = $this->em->find(TrainingSession::class, $sessionId);
        if (null === $session) {
            throw new UnavailableDataException(sprintf('cannot find training session with id: %s', $sessionId));
        }
        return $session;
    }

    private function findTrainingEnrollment(?string $trainingEnrollmentId): TrainingEnrollment
    {
        if (!$trainingEnrollmentId) {
            throw new InvalidActionInputException('trainingEnrollmentId is required');
        }

        $enrollment = $this->em->find(TrainingEnrollment::class, $trainingEnrollmentId);

        if (null === $enrollment) {
            throw new UnavailableDataException(sprintf('cannot find training enrollment with id: %s', $trainingEnrollmentId));
        }

        return $enrollment;
    }

    private function assertActionAllowed(TrainingEnrollment $enrollment, string $action): void
    {
        $allowed = TrainingEnrollmentConstants::getAllowedActionsForStatus($enrollment->getStatus());
        if (!\in_array($action, $allowed, true)) {
            throw new InvalidActionInputException('Action not allowed : invalid training enrollment state');
        }
    }

    private function applyAction(TrainingEnrollment $enrollment, string $action): void
    {
        $now = new \DateTimeImmutable();
        $actorId = $this->resolveActorId();

        match ($action) {
            TrainingEnrollmentConstants::ACTION_COMPLETE => $enrollment
                ->setStatus(TrainingEnrollmentConstants::STATUS_COMPLETED)
                ->setCompletedAt($now)
                ->setCompletedBy($actorId),
            TrainingEnrollmentConstants::ACTION_MARK_ABSENT => $enrollment
                ->setStatus(TrainingEnrollmentConstants::STATUS_ABSENT)
                ->setAbsentAt($now)
                ->setAbsentBy($actorId),
            TrainingEnrollmentConstants::ACTION_SET_ENROLLED => $enrollment
                ->setStatus(TrainingEnrollmentConstants::STATUS_ENROLLED)
                ->setEnrolledAt($now)
                ->setEnrolledBy($actorId)
                ->setCompletedAt(null)
                ->setCompletedBy(null)
                ->setAbsentAt(null)
                ->setAbsentBy(null),
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
