<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CloseEvaluationCycleDto;
use App\Entity\EvaluationCycle;
use App\Manager\EvaluationCycleManager;
use App\Model\CloseEvaluationCycleModel;

class CloseEvaluationCycleProcessor implements ProcessorInterface
{
    public function __construct(private EvaluationCycleManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): EvaluationCycle
    {
        /** @var CloseEvaluationCycleDto $data */
        return $this->manager->closeFrom(new CloseEvaluationCycleModel($data->evaluationCycleId));
    }
}
