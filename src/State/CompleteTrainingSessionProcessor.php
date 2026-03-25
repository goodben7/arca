<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CompleteTrainingSessionDto;
use App\Entity\TrainingSession;
use App\Manager\TrainingSessionManager;

class CompleteTrainingSessionProcessor implements ProcessorInterface
{
    public function __construct(private TrainingSessionManager $manager)
    {
    }

    /**
     * @param CompleteTrainingSessionDto $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TrainingSession
    {
        return $this->manager->complete($data->trainingSessionId);
    }
}

