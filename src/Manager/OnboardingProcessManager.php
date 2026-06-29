<?php

namespace App\Manager;

use App\Entity\Employee;
use App\Entity\OnboardingProcess;
use App\Entity\OnboardingTask;
use App\Entity\User;
use App\Event\ActivityEvent;
use App\Event\Domain\OnboardingCompletedEvent;
use App\Event\Domain\OnboardingStartedEvent;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Message\Query\GetUserDetails;
use App\Message\Query\QueryBusInterface;
use App\Model\CancelOnboardingProcessModel;
use App\Model\CompleteOnboardingProcessModel;
use App\Model\OnboardingProcessConstants;
use App\Model\OnboardingTaskConstants;
use App\Repository\OnboardingProcessRepository;
use App\Repository\OnboardingTaskRepository;
use App\Service\ActivityEventDispatcher;
use App\Service\OnboardingChecklistProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class OnboardingProcessManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private ActivityEventDispatcher $eventDispatcher,
        private Security $security,
        private QueryBusInterface $queries,
        private EventDispatcherInterface $domainEventDispatcher,
        private OnboardingProcessRepository $processRepository,
        private OnboardingTaskRepository $taskRepository,
        private OnboardingChecklistProvider $checklistProvider,
    ) {
    }

    public function startForEmployee(Employee $employee, ?string $actorId = null): OnboardingProcess
    {
        $employeeId = $employee->getId();
        if (null === $employeeId) {
            throw new \LogicException('Cannot start onboarding for employee without id');
        }

        if (null !== $this->processRepository->findActiveForEmployee($employeeId)) {
            throw new InvalidActionInputException('an active onboarding process already exists for this employee');
        }

        $now = new \DateTimeImmutable();
        $actor = $actorId ?? $this->resolveActorId();

        $process = (new OnboardingProcess())
            ->setEmployee($employeeId)
            ->setStatus(OnboardingProcessConstants::STATUS_IN_PROGRESS)
            ->setStartedAt($now);

        $this->em->persist($process);

        foreach ($this->checklistProvider->getDefaultItems() as $item) {
            $task = (new OnboardingTask())
                ->setProcess($process)
                ->setTitle($item['title'])
                ->setType($item['type'])
                ->setStatus(OnboardingTaskConstants::STATUS_PENDING)
                ->setDueDate($now->modify(sprintf('+%d days', $item['dueDays'])));
            $this->em->persist($task);
        }

        $this->em->flush();

        $this->eventDispatcher->dispatch($process, ActivityEvent::ACTION_CREATE);

        $this->domainEventDispatcher->dispatch(new OnboardingStartedEvent($process, $actor));

        return $process;
    }

    public function completeFrom(CompleteOnboardingProcessModel $model): OnboardingProcess
    {
        $process = $this->findProcess($model->onboardingProcessId);
        $this->assertActionAllowed($process, OnboardingProcessConstants::ACTION_COMPLETE);

        if ($this->taskRepository->countOpenByProcess($process) > 0) {
            throw new InvalidActionInputException('cannot complete onboarding while tasks are still open');
        }

        if (0 === $this->taskRepository->countCompletedByProcess($process)) {
            throw new InvalidActionInputException('cannot complete onboarding without at least one completed task');
        }

        return $this->markCompleted($process);
    }

    public function cancelFrom(CancelOnboardingProcessModel $model): OnboardingProcess
    {
        $process = $this->findProcess($model->onboardingProcessId);
        $this->assertActionAllowed($process, OnboardingProcessConstants::ACTION_CANCEL);

        $process
            ->setStatus(OnboardingProcessConstants::STATUS_CANCELLED)
            ->setCompletedAt(new \DateTimeImmutable());

        $this->em->flush();

        $this->eventDispatcher->dispatch($process, ActivityEvent::ACTION_EDIT, null, 'onboarding process cancelled');

        return $process;
    }

    public function tryAutoComplete(OnboardingProcess $process): ?OnboardingProcess
    {
        if (OnboardingProcessConstants::STATUS_IN_PROGRESS !== $process->getStatus()) {
            return null;
        }

        if ($this->taskRepository->countOpenByProcess($process) > 0) {
            return null;
        }

        if (0 === $this->taskRepository->countCompletedByProcess($process)) {
            return null;
        }

        return $this->markCompleted($process);
    }

    private function markCompleted(OnboardingProcess $process): OnboardingProcess
    {
        $process
            ->setStatus(OnboardingProcessConstants::STATUS_COMPLETED)
            ->setCompletedAt(new \DateTimeImmutable());

        $this->em->flush();

        $this->eventDispatcher->dispatch($process, ActivityEvent::ACTION_EDIT, null, 'onboarding process completed');

        $this->domainEventDispatcher->dispatch(
            new OnboardingCompletedEvent($process, $this->resolveActorId())
        );

        return $process;
    }

    private function assertActionAllowed(OnboardingProcess $process, string $action): void
    {
        $allowed = OnboardingProcessConstants::getAllowedActionsForStatus($process->getStatus());
        if (!\in_array($action, $allowed, true)) {
            throw new InvalidActionInputException('Action not allowed : invalid onboarding process state');
        }
    }

    private function findProcess(?string $processId): OnboardingProcess
    {
        if (!$processId) {
            throw new InvalidActionInputException('onboardingProcessId is required');
        }

        $process = $this->em->find(OnboardingProcess::class, $processId);

        if (null === $process) {
            throw new UnavailableDataException(sprintf('cannot find onboarding process with id: %s', $processId));
        }

        return $process;
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
