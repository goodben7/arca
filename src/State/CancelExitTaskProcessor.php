<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CancelExitTaskDto;
use App\Entity\ExitTask;
use App\Manager\ExitTaskManager;
use App\Model\CancelExitTaskModel;

class CancelExitTaskProcessor implements ProcessorInterface
{
    public function __construct(private ExitTaskManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ExitTask
    {
        /** @var CancelExitTaskDto $data */
        return $this->manager->cancelFrom(new CancelExitTaskModel($data->exitTaskId));
    }
}
