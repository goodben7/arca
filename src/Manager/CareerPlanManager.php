<?php

namespace App\Manager;

use App\Entity\CareerPlan;
use App\Entity\Employee;
use App\Event\ActivityEvent;
use App\Exception\UnavailableDataException;
use App\Model\CareerPlanConstants;
use App\Model\NewCareerPlanModel;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;

class CareerPlanManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private ActivityEventDispatcher $eventDispatcher,
        private JobRoleManager $jobRoles,
        private GradeManager $grades,
    ) {
    }

    public function createFrom(NewCareerPlanModel $model): CareerPlan
    {
        $employee = $this->findEmployee($model->employee);

        $plan = (new CareerPlan())
            ->setEmployee($employee->getId())
            ->setTargetJobRole($this->jobRoles->find($model->targetJobRoleId))
            ->setTargetDate($model->targetDate)
            ->setStatus(CareerPlanConstants::STATUS_ACTIVE)
            ->setNotes($model->notes);

        if (null !== $model->targetGradeId) {
            $plan->setTargetGrade($this->grades->find($model->targetGradeId));
        }

        $this->em->persist($plan);
        $this->em->flush();

        $this->eventDispatcher->dispatch($plan, ActivityEvent::ACTION_CREATE);

        return $plan;
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
