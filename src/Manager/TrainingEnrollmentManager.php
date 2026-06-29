<?php

namespace App\Manager;

use App\Entity\Employee;
use App\Entity\TrainingEnrollment;
use App\Entity\TrainingSession;
use App\Entity\User;
use App\Event\ActivityEvent;
use App\Event\Domain\TrainingEnrollmentCertifiedEvent;
use App\Event\Domain\TrainingEnrollmentCompletedEvent;
use App\Event\Domain\TrainingEnrollmentStartedEvent;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Message\Query\GetUserDetails;
use App\Message\Query\QueryBusInterface;
use App\Model\CertifyTrainingEnrollmentModel;
use App\Model\NewTrainingEnrollmentModel;
use App\Model\TrainingEnrollmentConstants;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class TrainingEnrollmentManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private ActivityEventDispatcher $eventDispatcher,
        private Security $security,
        private QueryBusInterface $queries,
        private EventDispatcherInterface $domainEventDispatcher,
    ) {
    }

    public function createFrom(NewTrainingEnrollmentModel $model): TrainingEnrollment
    {
        $employee = $this->findEmployee($model->employee);
        $session = $this->findTrainingSession($model->trainingSession);
        $actorId = $this->resolveActorId();
        $now = new \DateTimeImmutable();

        $enrollment = new TrainingEnrollment();
        $enrollment
            ->setEmployee($employee->getId())
            ->setTrainingSession($session->getId())
            ->setStatus(TrainingEnrollmentConstants::STATUS_ASSIGNED)
            ->setAssignedAt($now)
            ->setAssignedBy($actorId);

        $this->em->persist($enrollment);
        $this->em->flush();

        $this->eventDispatcher->dispatch($enrollment, ActivityEvent::ACTION_CREATE);

        return $enrollment;
    }

    public function start(string $trainingEnrollmentId): TrainingEnrollment
    {
        $enrollment = $this->findTrainingEnrollment($trainingEnrollmentId);
        $this->assertActionAllowed($enrollment, TrainingEnrollmentConstants::ACTION_START);
        $this->applyAction($enrollment, TrainingEnrollmentConstants::ACTION_START);
        $this->em->flush();

        $this->eventDispatcher->dispatch($enrollment, ActivityEvent::ACTION_EDIT, null, 'training enrollment started');
        $this->domainEventDispatcher->dispatch(
            new TrainingEnrollmentStartedEvent($enrollment, $this->resolveActorId())
        );

        return $enrollment;
    }

    public function complete(string $trainingEnrollmentId): TrainingEnrollment
    {
        $enrollment = $this->findTrainingEnrollment($trainingEnrollmentId);
        $this->assertActionAllowed($enrollment, TrainingEnrollmentConstants::ACTION_COMPLETE);
        $this->applyAction($enrollment, TrainingEnrollmentConstants::ACTION_COMPLETE);
        $this->em->flush();

        $this->eventDispatcher->dispatch($enrollment, ActivityEvent::ACTION_EDIT, null, 'training enrollment completed');
        $this->domainEventDispatcher->dispatch(
            new TrainingEnrollmentCompletedEvent($enrollment, $this->resolveActorId())
        );

        return $enrollment;
    }

    public function certifyFrom(CertifyTrainingEnrollmentModel $model): TrainingEnrollment
    {
        $enrollment = $this->findTrainingEnrollment($model->trainingEnrollmentId);
        $this->assertActionAllowed($enrollment, TrainingEnrollmentConstants::ACTION_CERTIFY);

        if (null !== $model->score) {
            $enrollment->setScore((string) $model->score);
        }

        if (null !== $model->certificate) {
            $enrollment->setCertificate($model->certificate);
        }

        if (null === $enrollment->getCertificate()) {
            throw new InvalidActionInputException('certificate is required to certify a training enrollment');
        }

        $this->applyAction($enrollment, TrainingEnrollmentConstants::ACTION_CERTIFY);
        $this->em->flush();

        $this->eventDispatcher->dispatch($enrollment, ActivityEvent::ACTION_EDIT, null, 'training enrollment certified');
        $this->domainEventDispatcher->dispatch(
            new TrainingEnrollmentCertifiedEvent($enrollment, $this->resolveActorId())
        );

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
        return $this->setAssigned($trainingEnrollmentId);
    }

    public function setAssigned(string $trainingEnrollmentId): TrainingEnrollment
    {
        $enrollment = $this->findTrainingEnrollment($trainingEnrollmentId);
        $this->assertActionAllowed($enrollment, TrainingEnrollmentConstants::ACTION_SET_ASSIGNED);
        $this->applyAction($enrollment, TrainingEnrollmentConstants::ACTION_SET_ASSIGNED);
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
        $status = TrainingEnrollmentConstants::normalizeStatus($enrollment->getStatus());
        $allowed = TrainingEnrollmentConstants::getAllowedActionsForStatus($status);
        if (!\in_array($action, $allowed, true)) {
            throw new InvalidActionInputException('Action not allowed : invalid training enrollment state');
        }
    }

    private function applyAction(TrainingEnrollment $enrollment, string $action): void
    {
        $now = new \DateTimeImmutable();
        $actorId = $this->resolveActorId();

        match ($action) {
            TrainingEnrollmentConstants::ACTION_START => $enrollment
                ->setStatus(TrainingEnrollmentConstants::STATUS_IN_PROGRESS)
                ->setStartedAt($now)
                ->setStartedBy($actorId),
            TrainingEnrollmentConstants::ACTION_COMPLETE => $enrollment
                ->setStatus(TrainingEnrollmentConstants::STATUS_COMPLETED)
                ->setCompletedAt($now)
                ->setCompletedBy($actorId),
            TrainingEnrollmentConstants::ACTION_CERTIFY => $enrollment
                ->setStatus(TrainingEnrollmentConstants::STATUS_CERTIFIED)
                ->setCertifiedAt($now)
                ->setCertifiedBy($actorId),
            TrainingEnrollmentConstants::ACTION_MARK_ABSENT => $enrollment
                ->setStatus(TrainingEnrollmentConstants::STATUS_ABSENT)
                ->setAbsentAt($now)
                ->setAbsentBy($actorId),
            TrainingEnrollmentConstants::ACTION_SET_ASSIGNED => $enrollment
                ->setStatus(TrainingEnrollmentConstants::STATUS_ASSIGNED)
                ->setAssignedAt($now)
                ->setAssignedBy($actorId)
                ->setStartedAt(null)
                ->setStartedBy(null)
                ->setCompletedAt(null)
                ->setCompletedBy(null)
                ->setCertifiedAt(null)
                ->setCertifiedBy(null)
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

        /** @var User|null $user */
        $user = $this->queries->ask(new GetUserDetails($identifier));

        return $user ? $user->getId() : 'SYSTEM';
    }
}
