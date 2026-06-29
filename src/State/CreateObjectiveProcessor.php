<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CreateObjectiveDto;
use App\Entity\Objective;
use App\Manager\ObjectiveManager;
use App\Model\NewObjectiveModel;

class CreateObjectiveProcessor implements ProcessorInterface
{
    public function __construct(private ObjectiveManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Objective
    {
        /** @var CreateObjectiveDto $data */
        return $this->manager->createFrom(new NewObjectiveModel(
            $data->employee,
            $data->evaluationCycleId,
            $data->title,
            $data->description,
            $data->specific,
            $data->measurable,
            $data->targetValue,
            $data->achievable,
            $data->relevant,
            $data->dueDate,
        ));
    }
}
