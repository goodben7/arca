<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\OpenEvaluationCycleDto;
use App\Entity\EvaluationCycle;
use App\Manager\EvaluationCycleManager;
use App\Model\OpenEvaluationCycleModel;

class OpenEvaluationCycleProcessor implements ProcessorInterface
{
    public function __construct(private EvaluationCycleManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): EvaluationCycle
    {
        /** @var OpenEvaluationCycleDto $data */
        return $this->manager->openFrom(new OpenEvaluationCycleModel($data->evaluationCycleId));
    }
}
