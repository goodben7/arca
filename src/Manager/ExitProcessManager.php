<?php

namespace App\Manager;

use App\Entity\Employee;
use App\Entity\ExitProcess;
use App\Entity\ExitTask;
use App\Entity\User;
use App\Event\ActivityEvent;
use App\Event\Domain\ExitProcessCompletedEvent;
use App\Event\Domain\ExitProcessStartedEvent;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Message\Query\GetUserDetails;
use App\Message\Query\QueryBusInterface;
use App\Model\CancelExitProcessModel;
use App\Model\CompleteExitProcessModel;
use App\Model\EmployeeConstants;
use App\Model\EndContractModel;
use App\Model\ExitProcessConstants;
use App\Model\ExitTaskConstants;
use App\Model\NewExitProcessModel;
use App\Model\RetireEmployeeModel;
use App\Model\StartExitProcessModel;
use App\Model\TerminateEmployeeModel;
use App\Repository\ContractRepository;
use App\Repository\ExitProcessRepository;
use App\Repository\ExitTaskRepository;
use App\Service\ActivityEventDispatcher;
use App\Service\OffboardingChecklistProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ExitProcessManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private ActivityEventDispatcher $eventDispatcher,
        private Security $security,
        private QueryBusInterface $queries,
        private EventDispatcherInterface $domainEventDispatcher,
        private ExitProcessRepository $processRepository,
        private ExitTaskRepository $taskRepository,
        private OffboardingChecklistProvider $checklistProvider,
        private ContractRepository $contracts,
        private ContractManager $contractsManager,
        private EmployeeManager $employees,
        private UserManager $users,
        private EmployeeBenefitManager $employeeBenefits,
    ) {
    }

    public function createFrom(NewExitProcessModel $model): ExitProcess
    {
        $employee = $this->findEmployee($model->employee);

        if (EmployeeConstants::STATUS_ACTIVE !== $employee->getStatus()) {
            throw new InvalidActionInputException('exit process can only be created for an active employee');
        }

        if (null !== $this->processRepository->findActiveForEmployee((string) $employee->getId())) {
            throw new InvalidActionInputException('an active exit process already exists for this employee');
        }

        $process = (new ExitProcess())
            ->setEmployee((string) $employee->getId())
            ->setReason($model->reason)
            ->setDepartureDate($model->departureDate)
            ->setStatus(ExitProcessConstants::STATUS_PENDING);

        $this->em->persist($process);
        $this->em->flush();

        $this->eventDispatcher->dispatch($process, ActivityEvent::ACTION_CREATE);

        return $process;
    }

    public function startFrom(StartExitProcessModel $model): ExitProcess
    {
        $process = $this->findProcess($model->exitProcessId);
        $this->assertActionAllowed($process, ExitProcessConstants::ACTION_START);

        $now = new \DateTimeImmutable();

        if (0 === $this->taskRepository->count(['process' => $process])) {
            foreach ($this->checklistProvider->getDefaultItems() as $item) {
                $task = (new ExitTask())
                    ->setProcess($process)
                    ->setTitle($item['title'])
                    ->setType($item['type'])
                    ->setStatus(ExitTaskConstants::STATUS_PENDING)
                    ->setDueDate($now->modify(sprintf('+%d days', $item['dueDays'])));
                $this->em->persist($task);
            }
        }

        $process
            ->setStatus(ExitProcessConstants::STATUS_IN_PROGRESS)
            ->setStartedAt($now);

        $this->em->flush();

        $this->eventDispatcher->dispatch($process, ActivityEvent::ACTION_EDIT, null, 'exit process started');

        $this->domainEventDispatcher->dispatch(
            new ExitProcessStartedEvent($process, $this->resolveActorId())
        );

        return $process;
    }

    public function completeFrom(CompleteExitProcessModel $model): ExitProcess
    {
        $process = $this->findProcess($model->exitProcessId);
        $this->assertActionAllowed($process, ExitProcessConstants::ACTION_COMPLETE);

        if ($this->taskRepository->countOpenByProcess($process) > 0) {
            throw new InvalidActionInputException('cannot complete exit process while tasks are still open');
        }

        if (0 === $this->taskRepository->countCompletedByProcess($process)) {
            throw new InvalidActionInputException('cannot complete exit process without at least one completed task');
        }

        $employee = $this->findEmployee((string) $process->getEmployee());
        $departureDate = $process->getDepartureDate() ?? new \DateTimeImmutable();
        $employee->setDepartureDate($departureDate);

        $contract = $this->contracts->findActiveByEmployee((string) $employee->getId());
        if (null !== $contract && null !== $contract->getId()) {
            $this->contractsManager->endFrom(new EndContractModel($contract->getId()));
        }

        if (ExitProcessConstants::isRetirementReason((string) $process->getReason())) {
            $this->employees->retireFrom(new RetireEmployeeModel((string) $employee->getId()));
        } else {
            $this->employees->terminateFrom(new TerminateEmployeeModel((string) $employee->getId()));
        }

        $userId = $employee->getUserId();
        if (null !== $userId) {
            $this->users->lockUser($userId);
        }

        $this->employeeBenefits->endActiveBenefitsForEmployee((string) $employee->getId(), $departureDate);

        $process
            ->setStatus(ExitProcessConstants::STATUS_COMPLETED)
            ->setCompletedAt(new \DateTimeImmutable());

        $this->em->flush();

        $this->eventDispatcher->dispatch($process, ActivityEvent::ACTION_EDIT, null, 'exit process completed');

        $this->domainEventDispatcher->dispatch(
            new ExitProcessCompletedEvent($process, $this->resolveActorId())
        );

        return $process;
    }

    public function cancelFrom(CancelExitProcessModel $model): ExitProcess
    {
        $process = $this->findProcess($model->exitProcessId);
        $this->assertActionAllowed($process, ExitProcessConstants::ACTION_CANCEL);

        $process
            ->setStatus(ExitProcessConstants::STATUS_CANCELLED)
            ->setCompletedAt(new \DateTimeImmutable());

        $this->em->flush();

        $this->eventDispatcher->dispatch($process, ActivityEvent::ACTION_EDIT, null, 'exit process cancelled');

        return $process;
    }

    public function tryAutoComplete(ExitProcess $process): ?ExitProcess
    {
        if (ExitProcessConstants::STATUS_IN_PROGRESS !== $process->getStatus()) {
            return null;
        }

        if ($this->taskRepository->countOpenByProcess($process) > 0) {
            return null;
        }

        if (0 === $this->taskRepository->countCompletedByProcess($process)) {
            return null;
        }

        return $this->completeFrom(new CompleteExitProcessModel($process->getId()));
    }

    private function assertActionAllowed(ExitProcess $process, string $action): void
    {
        $allowed = ExitProcessConstants::getAllowedActionsForStatus($process->getStatus());
        if (!\in_array($action, $allowed, true)) {
            throw new InvalidActionInputException('Action not allowed : invalid exit process state');
        }
    }

    private function findProcess(?string $processId): ExitProcess
    {
        if (!$processId) {
            throw new InvalidActionInputException('exitProcessId is required');
        }

        $process = $this->em->find(ExitProcess::class, $processId);

        if (null === $process) {
            throw new UnavailableDataException(sprintf('cannot find exit process with id: %s', $processId));
        }

        return $process;
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
