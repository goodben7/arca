<?php

namespace App\Manager;

use App\Entity\EvaluationCycle;
use App\Event\ActivityEvent;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Model\CloseEvaluationCycleModel;
use App\Model\EvaluationCycleConstants;
use App\Model\NewEvaluationCycleModel;
use App\Model\OpenEvaluationCycleModel;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;

class EvaluationCycleManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private ActivityEventDispatcher $eventDispatcher,
    ) {
    }

    public function createFrom(NewEvaluationCycleModel $model): EvaluationCycle
    {
        if ($model->startDate > $model->endDate) {
            throw new InvalidActionInputException('startDate must be before endDate');
        }

        $cycle = (new EvaluationCycle())
            ->setName($model->name)
            ->setYear($model->year)
            ->setStartDate($model->startDate)
            ->setEndDate($model->endDate)
            ->setStatus(EvaluationCycleConstants::STATUS_DRAFT);

        $this->em->persist($cycle);
        $this->em->flush();

        $this->eventDispatcher->dispatch($cycle, ActivityEvent::ACTION_CREATE);

        return $cycle;
    }

    public function openFrom(OpenEvaluationCycleModel $model): EvaluationCycle
    {
        $cycle = $this->findCycle($model->evaluationCycleId);
        $this->assertActionAllowed($cycle, EvaluationCycleConstants::ACTION_OPEN);

        $cycle
            ->setStatus(EvaluationCycleConstants::STATUS_OPEN)
            ->setOpenedAt(new \DateTimeImmutable());

        $this->em->flush();
        $this->eventDispatcher->dispatch($cycle, ActivityEvent::ACTION_EDIT, null, 'evaluation cycle opened');

        return $cycle;
    }

    public function closeFrom(CloseEvaluationCycleModel $model): EvaluationCycle
    {
        $cycle = $this->findCycle($model->evaluationCycleId);
        $this->assertActionAllowed($cycle, EvaluationCycleConstants::ACTION_CLOSE);

        $cycle
            ->setStatus(EvaluationCycleConstants::STATUS_CLOSED)
            ->setClosedAt(new \DateTimeImmutable());

        $this->em->flush();
        $this->eventDispatcher->dispatch($cycle, ActivityEvent::ACTION_EDIT, null, 'evaluation cycle closed');

        return $cycle;
    }

    public function find(string $id): EvaluationCycle
    {
        return $this->findCycle($id);
    }

    private function assertActionAllowed(EvaluationCycle $cycle, string $action): void
    {
        $allowed = EvaluationCycleConstants::getAllowedActionsForStatus($cycle->getStatus());
        if (!\in_array($action, $allowed, true)) {
            throw new InvalidActionInputException('Action not allowed : invalid evaluation cycle state');
        }
    }

    private function findCycle(?string $cycleId): EvaluationCycle
    {
        if (!$cycleId) {
            throw new InvalidActionInputException('evaluationCycleId is required');
        }

        $cycle = $this->em->find(EvaluationCycle::class, $cycleId);

        if (null === $cycle) {
            throw new UnavailableDataException(sprintf('cannot find evaluation cycle with id: %s', $cycleId));
        }

        return $cycle;
    }
}
