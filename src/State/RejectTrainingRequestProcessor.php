<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\RejectTrainingRequestDto;
use App\Entity\TrainingRequest;
use App\Manager\TrainingRequestManager;

class RejectTrainingRequestProcessor implements ProcessorInterface
{
    public function __construct(private TrainingRequestManager $manager)
    {
    }

    /**
     * @param RejectTrainingRequestDto $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TrainingRequest
    {
        return $this->manager->reject($data->trainingRequestId, $data->reason ?? null);
    }
}

