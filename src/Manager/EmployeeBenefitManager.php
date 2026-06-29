<?php

namespace App\Manager;

use App\Entity\Benefit;
use App\Entity\Employee;
use App\Entity\EmployeeBenefit;
use App\Event\ActivityEvent;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Model\EmployeeBenefitConstants;
use App\Model\NewEmployeeBenefitModel;
use App\Repository\EmployeeBenefitRepository;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;

class EmployeeBenefitManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private ActivityEventDispatcher $eventDispatcher,
        private EmployeeBenefitRepository $employeeBenefits,
    ) {
    }

    public function createFrom(NewEmployeeBenefitModel $model): EmployeeBenefit
    {
        $employee = $this->findEmployee($model->employee);
        $benefit = $this->findBenefit($model->benefitId);

        if (null !== $this->employeeBenefits->findOneBy([
            'employee' => $employee->getId(),
            'benefit' => $benefit,
            'status' => EmployeeBenefitConstants::STATUS_ACTIVE,
        ])) {
            throw new InvalidActionInputException('employee already has an active enrollment for this benefit');
        }

        $enrollment = (new EmployeeBenefit())
            ->setEmployee((string) $employee->getId())
            ->setBenefit($benefit)
            ->setStartDate($model->startDate)
            ->setEndDate($model->endDate)
            ->setStatus(EmployeeBenefitConstants::STATUS_ACTIVE);

        $this->em->persist($enrollment);
        $this->em->flush();

        $this->eventDispatcher->dispatch($enrollment, ActivityEvent::ACTION_CREATE);

        return $enrollment;
    }

    public function endActiveBenefitsForEmployee(string $employeeId, \DateTimeImmutable $endDate): void
    {
        $benefits = $this->employeeBenefits->findBy([
            'employee' => $employeeId,
            'status' => EmployeeBenefitConstants::STATUS_ACTIVE,
        ]);

        foreach ($benefits as $benefit) {
            $benefit
                ->setStatus(EmployeeBenefitConstants::STATUS_ENDED)
                ->setEndDate($endDate);
        }

        if ([] !== $benefits) {
            $this->em->flush();
        }
    }

    private function findEmployee(string $employeeId): Employee
    {
        $employee = $this->em->find(Employee::class, $employeeId);

        if (null === $employee) {
            throw new UnavailableDataException(sprintf('cannot find employee with id: %s', $employeeId));
        }

        return $employee;
    }

    private function findBenefit(string $benefitId): Benefit
    {
        $benefit = $this->em->find(Benefit::class, $benefitId);

        if (null === $benefit) {
            throw new UnavailableDataException(sprintf('cannot find benefit with id: %s', $benefitId));
        }

        return $benefit;
    }
}
