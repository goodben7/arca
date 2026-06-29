<?php

namespace App\Manager;

use App\Entity\Employee;
use App\Entity\EvaluationCycle;
use App\Entity\PerformanceReview;
use App\Entity\User;
use App\Event\ActivityEvent;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Message\Query\GetUserDetails;
use App\Message\Query\QueryBusInterface;
use App\Model\EvaluationCycleConstants;
use App\Model\NewPerformanceReviewModel;
use App\Model\PerformanceReviewConstants;
use App\Model\SubmitPerformanceReviewModel;
use App\Model\ValidatePerformanceReviewModel;
use App\Repository\PerformanceReviewRepository;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class PerformanceReviewManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private ActivityEventDispatcher $eventDispatcher,
        private Security $security,
        private QueryBusInterface $queries,
        private EvaluationCycleManager $cycles,
        private PerformanceReviewRepository $reviewRepository,
    ) {
    }

    public function createFrom(NewPerformanceReviewModel $model): PerformanceReview
    {
        $employee = $this->findEmployee($model->employee);
        $cycle = $this->cycles->find($model->evaluationCycleId);

        if (EvaluationCycleConstants::STATUS_OPEN !== $cycle->getStatus()) {
            throw new InvalidActionInputException('performance reviews can only be created for an open evaluation cycle');
        }

        if (null !== $this->reviewRepository->findOneBy(['employee' => $employee->getId(), 'cycle' => $cycle])) {
            throw new InvalidActionInputException('a performance review already exists for this employee and cycle');
        }

        $review = (new PerformanceReview())
            ->setEmployee($employee->getId())
            ->setCycle($cycle)
            ->setReviewer($model->reviewer)
            ->setScore(null !== $model->score ? (string) $model->score : null)
            ->setComment($model->comment)
            ->setStatus(PerformanceReviewConstants::STATUS_DRAFT);

        $this->em->persist($review);
        $this->em->flush();

        $this->eventDispatcher->dispatch($review, ActivityEvent::ACTION_CREATE);

        return $review;
    }

    public function submitFrom(SubmitPerformanceReviewModel $model): PerformanceReview
    {
        $review = $this->findReview($model->performanceReviewId);
        $this->assertActionAllowed($review, PerformanceReviewConstants::ACTION_SUBMIT);

        if (null === $review->getScore()) {
            throw new InvalidActionInputException('score is required before submitting a performance review');
        }

        $review
            ->setStatus(PerformanceReviewConstants::STATUS_SUBMITTED)
            ->setSubmittedAt(new \DateTimeImmutable());

        $this->em->flush();
        $this->eventDispatcher->dispatch($review, ActivityEvent::ACTION_EDIT, null, 'performance review submitted');

        return $review;
    }

    public function validateFrom(ValidatePerformanceReviewModel $model): PerformanceReview
    {
        $review = $this->findReview($model->performanceReviewId);
        $this->assertActionAllowed($review, PerformanceReviewConstants::ACTION_VALIDATE);

        $review
            ->setStatus(PerformanceReviewConstants::STATUS_VALIDATED)
            ->setValidatedAt(new \DateTimeImmutable())
            ->setValidatedBy($this->resolveActorId());

        $this->em->flush();
        $this->eventDispatcher->dispatch($review, ActivityEvent::ACTION_EDIT, null, 'performance review validated');

        return $review;
    }

    private function assertActionAllowed(PerformanceReview $review, string $action): void
    {
        $allowed = PerformanceReviewConstants::getAllowedActionsForStatus($review->getStatus());
        if (!\in_array($action, $allowed, true)) {
            throw new InvalidActionInputException('Action not allowed : invalid performance review state');
        }
    }

    private function findReview(?string $reviewId): PerformanceReview
    {
        if (!$reviewId) {
            throw new InvalidActionInputException('performanceReviewId is required');
        }

        $review = $this->em->find(PerformanceReview::class, $reviewId);

        if (null === $review) {
            throw new UnavailableDataException(sprintf('cannot find performance review with id: %s', $reviewId));
        }

        return $review;
    }

    private function findEmployee(string $employeeId): Employee
    {
        $employee = $this->em->find(Employee::class, $employeeId);

        if (null === $employee) {
            throw new UnavailableDataException(sprintf('cannot find employee with id: %s', $employeeId));
        }

        return $employee;
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
