<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\StartExitTaskDto;
use App\Entity\ExitTask;
use App\Manager\ExitTaskManager;
use App\Model\StartExitTaskModel;

class StartExitTaskProcessor implements ProcessorInterface
{
    public function __construct(private ExitTaskManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ExitTask
    {
        /** @var StartExitTaskDto $data */
        return $this->manager->startFrom(new StartExitTaskModel($data->exitTaskId));
    }
}
