<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CancelObjectiveDto;
use App\Entity\Objective;
use App\Manager\ObjectiveManager;
use App\Model\CancelObjectiveModel;

class CancelObjectiveProcessor implements ProcessorInterface
{
    public function __construct(private ObjectiveManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Objective
    {
        /** @var CancelObjectiveDto $data */
        return $this->manager->cancelFrom(new CancelObjectiveModel($data->objectiveId));
    }
}
