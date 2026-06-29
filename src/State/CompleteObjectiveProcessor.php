<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CompleteObjectiveDto;
use App\Entity\Objective;
use App\Manager\ObjectiveManager;
use App\Model\CompleteObjectiveModel;

class CompleteObjectiveProcessor implements ProcessorInterface
{
    public function __construct(private ObjectiveManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Objective
    {
        /** @var CompleteObjectiveDto $data */
        return $this->manager->completeFrom(new CompleteObjectiveModel($data->objectiveId));
    }
}
