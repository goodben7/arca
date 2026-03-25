<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CreateTrainingSessionDto;
use App\Entity\TrainingSession;
use App\Manager\TrainingSessionManager;
use App\Model\NewTrainingSessionModel;

class CreateTrainingSessionProcessor implements ProcessorInterface
{
    public function __construct(private TrainingSessionManager $manager)
    {
    }

    /**
     * @param CreateTrainingSessionDto $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TrainingSession
    {
        $model = new NewTrainingSessionModel(
            $data->title,
            $data->trainer,
            $data->startDate,
            $data->endDate,
            $data->location,
            $data->capacity,
            $data->trainingRequest,
        );

        return $this->manager->createFrom($model);
    }
}

