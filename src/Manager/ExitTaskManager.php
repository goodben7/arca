<?php

namespace App\Manager;

use App\Entity\ExitProcess;
use App\Entity\ExitTask;
use App\Event\ActivityEvent;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Model\CancelExitTaskModel;
use App\Model\CompleteExitTaskModel;
use App\Model\ExitProcessConstants;
use App\Model\ExitTaskConstants;
use App\Model\StartExitTaskModel;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;

class ExitTaskManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private ActivityEventDispatcher $eventDispatcher,
        private ExitProcessManager $processes,
    ) {
    }

    public function startFrom(StartExitTaskModel $model): ExitTask
    {
        $task = $this->findTask($model->exitTaskId);
        $this->assertActionAllowed($task, ExitTaskConstants::ACTION_START);

        $task->setStatus(ExitTaskConstants::STATUS_IN_PROGRESS);

        $process = $task->getProcess();
        if ($process instanceof ExitProcess
            && ExitProcessConstants::STATUS_PENDING === $process->getStatus()
        ) {
            throw new InvalidActionInputException('start the exit process before working on tasks');
        }

        $this->em->flush();

        $this->eventDispatcher->dispatch($task, ActivityEvent::ACTION_EDIT, null, 'exit task started');

        return $task;
    }

    public function completeFrom(CompleteExitTaskModel $model): ExitTask
    {
        $task = $this->findTask($model->exitTaskId);
        $this->assertActionAllowed($task, ExitTaskConstants::ACTION_COMPLETE);

        $task->setStatus(ExitTaskConstants::STATUS_COMPLETED);
        $this->em->flush();

        $this->eventDispatcher->dispatch($task, ActivityEvent::ACTION_EDIT, null, 'exit task completed');

        $process = $task->getProcess();
        if ($process instanceof ExitProcess) {
            $this->processes->tryAutoComplete($process);
        }

        return $task;
    }

    public function cancelFrom(CancelExitTaskModel $model): ExitTask
    {
        $task = $this->findTask($model->exitTaskId);
        $this->assertActionAllowed($task, ExitTaskConstants::ACTION_CANCEL);

        $task->setStatus(ExitTaskConstants::STATUS_CANCELLED);
        $this->em->flush();

        $this->eventDispatcher->dispatch($task, ActivityEvent::ACTION_EDIT, null, 'exit task cancelled');

        $process = $task->getProcess();
        if ($process instanceof ExitProcess) {
            $this->processes->tryAutoComplete($process);
        }

        return $task;
    }

    private function assertActionAllowed(ExitTask $task, string $action): void
    {
        $allowed = ExitTaskConstants::getAllowedActionsForStatus($task->getStatus());
        if (!\in_array($action, $allowed, true)) {
            throw new InvalidActionInputException('Action not allowed : invalid exit task state');
        }
    }

    private function findTask(?string $taskId): ExitTask
    {
        if (!$taskId) {
            throw new InvalidActionInputException('exitTaskId is required');
        }

        $task = $this->em->find(ExitTask::class, $taskId);

        if (null === $task) {
            throw new UnavailableDataException(sprintf('cannot find exit task with id: %s', $taskId));
        }

        return $task;
    }
}
