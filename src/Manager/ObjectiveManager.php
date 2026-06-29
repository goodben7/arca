<?php

namespace App\Manager;

use App\Entity\Employee;
use App\Entity\EvaluationCycle;
use App\Entity\Objective;
use App\Event\ActivityEvent;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Model\ActivateObjectiveModel;
use App\Model\CancelObjectiveModel;
use App\Model\CompleteObjectiveModel;
use App\Model\EvaluationCycleConstants;
use App\Model\NewObjectiveModel;
use App\Model\ObjectiveConstants;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;

class ObjectiveManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private ActivityEventDispatcher $eventDispatcher,
        private EvaluationCycleManager $cycles,
    ) {
    }

    public function createFrom(NewObjectiveModel $model): Objective
    {
        $employee = $this->findEmployee($model->employee);
        $cycle = $this->cycles->find($model->evaluationCycleId);

        if (!\in_array($cycle->getStatus(), [EvaluationCycleConstants::STATUS_OPEN, EvaluationCycleConstants::STATUS_DRAFT], true)) {
            throw new InvalidActionInputException('objectives can only be created for a draft or open evaluation cycle');
        }

        $objective = (new Objective())
            ->setEmployee($employee->getId())
            ->setCycle($cycle)
            ->setTitle($model->title)
            ->setDescription($model->description)
            ->setSpecific($model->specific)
            ->setMeasurable($model->measurable)
            ->setTargetValue($model->targetValue)
            ->setAchievable($model->achievable)
            ->setRelevant($model->relevant)
            ->setDueDate($model->dueDate)
            ->setStatus(ObjectiveConstants::STATUS_DRAFT);

        $this->em->persist($objective);
        $this->em->flush();

        $this->eventDispatcher->dispatch($objective, ActivityEvent::ACTION_CREATE);

        return $objective;
    }

    public function activateFrom(ActivateObjectiveModel $model): Objective
    {
        $objective = $this->findObjective($model->objectiveId);
        $this->assertActionAllowed($objective, ObjectiveConstants::ACTION_ACTIVATE);

        $objective->setStatus(ObjectiveConstants::STATUS_ACTIVE);
        $this->em->flush();

        $this->eventDispatcher->dispatch($objective, ActivityEvent::ACTION_EDIT, null, 'objective activated');

        return $objective;
    }

    public function completeFrom(CompleteObjectiveModel $model): Objective
    {
        $objective = $this->findObjective($model->objectiveId);
        $this->assertActionAllowed($objective, ObjectiveConstants::ACTION_COMPLETE);

        $objective->setStatus(ObjectiveConstants::STATUS_COMPLETED);
        $this->em->flush();

        $this->eventDispatcher->dispatch($objective, ActivityEvent::ACTION_EDIT, null, 'objective completed');

        return $objective;
    }

    public function cancelFrom(CancelObjectiveModel $model): Objective
    {
        $objective = $this->findObjective($model->objectiveId);
        $this->assertActionAllowed($objective, ObjectiveConstants::ACTION_CANCEL);

        $objective->setStatus(ObjectiveConstants::STATUS_CANCELLED);
        $this->em->flush();

        $this->eventDispatcher->dispatch($objective, ActivityEvent::ACTION_EDIT, null, 'objective cancelled');

        return $objective;
    }

    private function assertActionAllowed(Objective $objective, string $action): void
    {
        $allowed = ObjectiveConstants::getAllowedActionsForStatus($objective->getStatus());
        if (!\in_array($action, $allowed, true)) {
            throw new InvalidActionInputException('Action not allowed : invalid objective state');
        }
    }

    private function findObjective(?string $objectiveId): Objective
    {
        if (!$objectiveId) {
            throw new InvalidActionInputException('objectiveId is required');
        }

        $objective = $this->em->find(Objective::class, $objectiveId);

        if (null === $objective) {
            throw new UnavailableDataException(sprintf('cannot find objective with id: %s', $objectiveId));
        }

        return $objective;
    }

    private function findEmployee(string $employeeId): Employee
    {
        $employee = $this->em->find(Employee::class, $employeeId);

        if (null === $employee) {
            throw new UnavailableDataException(sprintf('cannot find employee with id: %s', $employeeId));
        }

        return $employee;
    }
}
