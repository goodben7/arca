<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CancelTrainingSessionDto;
use App\Entity\TrainingSession;
use App\Manager\TrainingSessionManager;

class CancelTrainingSessionProcessor implements ProcessorInterface
{
    public function __construct(private TrainingSessionManager $manager)
    {
    }

    /**
     * @param CancelTrainingSessionDto $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TrainingSession
    {
        return $this->manager->cancel($data->trainingSessionId, $data->reason ?? null);
    }
}

