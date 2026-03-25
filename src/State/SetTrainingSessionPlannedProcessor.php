<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\SetTrainingSessionPlannedDto;
use App\Entity\TrainingSession;
use App\Manager\TrainingSessionManager;

class SetTrainingSessionPlannedProcessor implements ProcessorInterface
{
    public function __construct(private TrainingSessionManager $manager)
    {
    }

    /**
     * @param SetTrainingSessionPlannedDto $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TrainingSession
    {
        return $this->manager->setPlanned($data->trainingSessionId);
    }
}

