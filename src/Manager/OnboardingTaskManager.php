<?php

namespace App\Manager;

use App\Entity\OnboardingProcess;
use App\Entity\OnboardingTask;
use App\Entity\User;
use App\Event\ActivityEvent;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Message\Query\GetUserDetails;
use App\Message\Query\QueryBusInterface;
use App\Model\CancelOnboardingTaskModel;
use App\Model\CompleteOnboardingTaskModel;
use App\Model\OnboardingProcessConstants;
use App\Model\OnboardingTaskConstants;
use App\Model\StartOnboardingTaskModel;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class OnboardingTaskManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private ActivityEventDispatcher $eventDispatcher,
        private Security $security,
        private QueryBusInterface $queries,
        private OnboardingProcessManager $processes,
    ) {
    }

    public function startFrom(StartOnboardingTaskModel $model): OnboardingTask
    {
        $task = $this->findTask($model->onboardingTaskId);
        $this->assertActionAllowed($task, OnboardingTaskConstants::ACTION_START);

        $task->setStatus(OnboardingTaskConstants::STATUS_IN_PROGRESS);

        $process = $task->getProcess();
        if ($process instanceof OnboardingProcess
            && OnboardingProcessConstants::STATUS_PENDING === $process->getStatus()
        ) {
            $process
                ->setStatus(OnboardingProcessConstants::STATUS_IN_PROGRESS)
                ->setStartedAt($process->getStartedAt() ?? new \DateTimeImmutable());
        }

        $this->em->flush();

        $this->eventDispatcher->dispatch($task, ActivityEvent::ACTION_EDIT, null, 'onboarding task started');

        return $task;
    }

    public function completeFrom(CompleteOnboardingTaskModel $model): OnboardingTask
    {
        $task = $this->findTask($model->onboardingTaskId);
        $this->assertActionAllowed($task, OnboardingTaskConstants::ACTION_COMPLETE);

        $task->setStatus(OnboardingTaskConstants::STATUS_COMPLETED);
        $this->em->flush();

        $this->eventDispatcher->dispatch($task, ActivityEvent::ACTION_EDIT, null, 'onboarding task completed');

        $process = $task->getProcess();
        if ($process instanceof OnboardingProcess) {
            $this->processes->tryAutoComplete($process);
        }

        return $task;
    }

    public function cancelFrom(CancelOnboardingTaskModel $model): OnboardingTask
    {
        $task = $this->findTask($model->onboardingTaskId);
        $this->assertActionAllowed($task, OnboardingTaskConstants::ACTION_CANCEL);

        $task->setStatus(OnboardingTaskConstants::STATUS_CANCELLED);
        $this->em->flush();

        $this->eventDispatcher->dispatch($task, ActivityEvent::ACTION_EDIT, null, 'onboarding task cancelled');

        $process = $task->getProcess();
        if ($process instanceof OnboardingProcess) {
            $this->processes->tryAutoComplete($process);
        }

        return $task;
    }

    private function assertActionAllowed(OnboardingTask $task, string $action): void
    {
        $allowed = OnboardingTaskConstants::getAllowedActionsForStatus($task->getStatus());
        if (!\in_array($action, $allowed, true)) {
            throw new InvalidActionInputException('Action not allowed : invalid onboarding task state');
        }
    }

    private function findTask(?string $taskId): OnboardingTask
    {
        if (!$taskId) {
            throw new InvalidActionInputException('onboardingTaskId is required');
        }

        $task = $this->em->find(OnboardingTask::class, $taskId);

        if (null === $task) {
            throw new UnavailableDataException(sprintf('cannot find onboarding task with id: %s', $taskId));
        }

        return $task;
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
