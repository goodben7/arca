<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CancelExitProcessDto;
use App\Entity\ExitProcess;
use App\Manager\ExitProcessManager;
use App\Model\CancelExitProcessModel;

class CancelExitProcessProcessor implements ProcessorInterface
{
    public function __construct(private ExitProcessManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ExitProcess
    {
        /** @var CancelExitProcessDto $data */
        return $this->manager->cancelFrom(new CancelExitProcessModel($data->exitProcessId));
    }
}
