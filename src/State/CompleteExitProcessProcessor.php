<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CompleteExitProcessDto;
use App\Entity\ExitProcess;
use App\Manager\ExitProcessManager;
use App\Model\CompleteExitProcessModel;

class CompleteExitProcessProcessor implements ProcessorInterface
{
    public function __construct(private ExitProcessManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ExitProcess
    {
        /** @var CompleteExitProcessDto $data */
        return $this->manager->completeFrom(new CompleteExitProcessModel($data->exitProcessId));
    }
}
