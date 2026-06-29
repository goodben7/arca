<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CreateExitProcessDto;
use App\Entity\ExitProcess;
use App\Manager\ExitProcessManager;
use App\Model\NewExitProcessModel;

class CreateExitProcessProcessor implements ProcessorInterface
{
    public function __construct(private ExitProcessManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ExitProcess
    {
        /** @var CreateExitProcessDto $data */
        return $this->manager->createFrom(new NewExitProcessModel(
            $data->employee,
            $data->reason,
            $data->departureDate,
        ));
    }
}
