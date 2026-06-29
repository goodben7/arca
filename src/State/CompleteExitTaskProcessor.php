<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CompleteExitTaskDto;
use App\Entity\ExitTask;
use App\Manager\ExitTaskManager;
use App\Model\CompleteExitTaskModel;

class CompleteExitTaskProcessor implements ProcessorInterface
{
    public function __construct(private ExitTaskManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ExitTask
    {
        /** @var CompleteExitTaskDto $data */
        return $this->manager->completeFrom(new CompleteExitTaskModel($data->exitTaskId));
    }
}
