<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\StartExitProcessDto;
use App\Entity\ExitProcess;
use App\Manager\ExitProcessManager;
use App\Model\StartExitProcessModel;

class StartExitProcessProcessor implements ProcessorInterface
{
    public function __construct(private ExitProcessManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ExitProcess
    {
        /** @var StartExitProcessDto $data */
        return $this->manager->startFrom(new StartExitProcessModel($data->exitProcessId));
    }
}
