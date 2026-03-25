<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\ApproveTrainingRequestDto;
use App\Entity\TrainingRequest;
use App\Manager\TrainingRequestManager;

class ApproveTrainingRequestProcessor implements ProcessorInterface
{
    public function __construct(private TrainingRequestManager $manager)
    {
    }

    /**
     * @param ApproveTrainingRequestDto $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TrainingRequest
    {
        return $this->manager->approve($data->trainingRequestId);
    }
}

