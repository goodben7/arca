<?php

namespace App\Manager;

use App\Compensation\CompensationPolicyInterface;
use App\Entity\CompensationHistory;
use App\Entity\Contract;
use App\Entity\Employee;
use App\Entity\MobilityRequest;
use App\Event\ActivityEvent;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Message\NotifyPayrollMessage;
use App\Model\CompensationHistoryConstants;
use App\Model\MobilityRequestConstants;
use App\Model\RecordCompensationHistoryModel;
use App\Repository\ContractRepository;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CompensationHistoryManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private ActivityEventDispatcher $eventDispatcher,
        private ContractRepository $contracts,
        private CompensationPolicyInterface $compensationPolicy,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function recordFrom(RecordCompensationHistoryModel $model): CompensationHistory
    {
        $employee = $this->findEmployee((string) $model->employee);
        $contract = $this->findActiveContract($employee);

        return $this->persistChange(
            employee: $employee,
            contract: $contract,
            oldSalary: (string) $contract->getSalary(),
            newSalary: (string) $model->newSalary,
            effectiveDate: $model->effectiveDate,
            reason: (string) $model->reason,
            sourceEvent: CompensationHistoryConstants::SOURCE_MANUAL,
        );
    }

    public function recordFromMobility(MobilityRequest $request): ?CompensationHistory
    {
        if (!\in_array($request->getType(), [
            MobilityRequestConstants::TYPE_PROMOTION,
            MobilityRequestConstants::TYPE_DEMOTION,
        ], true)) {
            return null;
        }

        $employee = $this->findEmployee((string) $request->getEmployee());
        $contract = $this->contracts->findActiveByEmployee((string) $employee->getId());

        if (null === $contract || null === $contract->getSalary()) {
            return null;
        }

        $proposal = $this->compensationPolicy->computeNewSalary(
            $employee,
            (string) $contract->getSalary(),
            ['mobilityRequestId' => $request->getId()],
        );

        if (null === $proposal) {
            return null;
        }

        $effectiveDate = $request->getImplementedAt() ?? new \DateTimeImmutable();

        return $this->persistChange(
            employee: $employee,
            contract: $contract,
            oldSalary: (string) $contract->getSalary(),
            newSalary: $proposal->getNewSalary(),
            effectiveDate: $effectiveDate,
            reason: $proposal->getReason(),
            sourceEvent: CompensationHistoryConstants::SOURCE_MOBILITY_IMPLEMENTED,
        );
    }

    private function persistChange(
        Employee $employee,
        Contract $contract,
        string $oldSalary,
        string $newSalary,
        \DateTimeImmutable $effectiveDate,
        string $reason,
        string $sourceEvent,
    ): CompensationHistory {
        if (0 === bccomp($oldSalary, $newSalary, 2)) {
            throw new InvalidActionInputException('new salary must differ from current salary');
        }

        $history = (new CompensationHistory())
            ->setEmployee((string) $employee->getId())
            ->setOldSalary($oldSalary)
            ->setNewSalary($newSalary)
            ->setEffectiveDate($effectiveDate)
            ->setReason($reason)
            ->setSourceEvent($sourceEvent);

        $contract->setSalary($newSalary);

        $this->em->persist($history);
        $this->em->flush();

        $this->eventDispatcher->dispatch($history, ActivityEvent::ACTION_CREATE);

        $this->messageBus->dispatch(new NotifyPayrollMessage(
            (string) $history->getId(),
            (string) $employee->getId(),
            $oldSalary,
            $newSalary,
            $effectiveDate,
            $reason,
        ));

        return $history;
    }

    private function findEmployee(string $employeeId): Employee
    {
        $employee = $this->em->find(Employee::class, $employeeId);

        if (null === $employee) {
            throw new UnavailableDataException(sprintf('cannot find employee with id: %s', $employeeId));
        }

        return $employee;
    }

    private function findActiveContract(Employee $employee): Contract
    {
        $contract = $this->contracts->findActiveByEmployee((string) $employee->getId());

        if (null === $contract || null === $contract->getSalary()) {
            throw new UnavailableDataException(sprintf('no active contract with salary found for employee: %s', $employee->getId()));
        }

        return $contract;
    }
}
