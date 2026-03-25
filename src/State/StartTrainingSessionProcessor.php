<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\StartTrainingSessionDto;
use App\Entity\TrainingSession;
use App\Manager\TrainingSessionManager;

class StartTrainingSessionProcessor implements ProcessorInterface
{
    public function __construct(private TrainingSessionManager $manager)
    {
    }

    /**
     * @param StartTrainingSessionDto $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TrainingSession
    {
        return $this->manager->start($data->trainingSessionId);
    }
}

