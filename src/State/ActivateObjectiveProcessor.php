<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\ActivateObjectiveDto;
use App\Entity\Objective;
use App\Manager\ObjectiveManager;
use App\Model\ActivateObjectiveModel;

class ActivateObjectiveProcessor implements ProcessorInterface
{
    public function __construct(private ObjectiveManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Objective
    {
        /** @var ActivateObjectiveDto $data */
        return $this->manager->activateFrom(new ActivateObjectiveModel($data->objectiveId));
    }
}
