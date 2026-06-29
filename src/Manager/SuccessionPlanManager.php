<?php

namespace App\Manager;

use App\Entity\Employee;
use App\Entity\SuccessionPlan;
use App\Event\ActivityEvent;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Model\EmployeeConstants;
use App\Model\NewSuccessionPlanModel;
use App\Model\SuccessionPlanConstants;
use App\Repository\SuccessionPlanRepository;
use App\Service\ActivityEventDispatcher;
use App\Service\CriticalJobRolesProvider;
use Doctrine\ORM\EntityManagerInterface;

class SuccessionPlanManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private ActivityEventDispatcher $eventDispatcher,
        private JobRoleManager $jobRoles,
        private SuccessionPlanRepository $successionPlans,
        private CriticalJobRolesProvider $criticalJobRoles,
    ) {
    }

    public function createFrom(NewSuccessionPlanModel $model): SuccessionPlan
    {
        $criticalJobRole = $this->jobRoles->find($model->criticalJobRoleId);
        $candidate = $this->findEmployee($model->candidate);

        if (EmployeeConstants::STATUS_ACTIVE !== $candidate->getStatus()) {
            throw new InvalidActionInputException('succession candidate must be an active employee');
        }

        if (!$this->isCriticalJobRole($criticalJobRole->getId())) {
            throw new InvalidActionInputException('job role is not configured as critical for succession');
        }

        if (null !== $this->successionPlans->findActiveByCriticalJobRoleAndCandidate(
            (string) $criticalJobRole->getId(),
            (string) $candidate->getId(),
        )) {
            throw new InvalidActionInputException('an active succession plan already exists for this candidate and role');
        }

        $plan = (new SuccessionPlan())
            ->setCriticalJobRole($criticalJobRole)
            ->setCandidate((string) $candidate->getId())
            ->setReadinessLevel($model->readinessLevel)
            ->setStatus(SuccessionPlanConstants::STATUS_ACTIVE)
            ->setNotes($model->notes);

        $this->em->persist($plan);
        $this->em->flush();

        $this->eventDispatcher->dispatch($plan, ActivityEvent::ACTION_CREATE);

        return $plan;
    }

    private function isCriticalJobRole(?string $jobRoleId): bool
    {
        if (null === $jobRoleId) {
            return false;
        }

        return \in_array($jobRoleId, $this->criticalJobRoles->getCriticalJobRoleIds(), true);
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
