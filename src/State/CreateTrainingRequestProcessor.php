<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\TrainingRequest;
use App\Manager\TrainingRequestManager;
use App\Model\NewTrainingRequestModel;

class CreateTrainingRequestProcessor implements ProcessorInterface
{
    public function __construct(private TrainingRequestManager $manager)
    {
    }

    /**
     * @param NewTrainingRequestModel $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TrainingRequest
    {
        $model = new NewTrainingRequestModel(
            $data->department,
            $data->title,
            $data->description,
            $data->numberOfParticipants,
            $data->priority,
        );

        return $this->manager->createFrom($model);
    }
}

