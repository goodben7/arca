<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CreateEvaluationCycleDto;
use App\Entity\EvaluationCycle;
use App\Manager\EvaluationCycleManager;
use App\Model\NewEvaluationCycleModel;

class CreateEvaluationCycleProcessor implements ProcessorInterface
{
    public function __construct(private EvaluationCycleManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): EvaluationCycle
    {
        /** @var CreateEvaluationCycleDto $data */
        return $this->manager->createFrom(new NewEvaluationCycleModel(
            $data->name,
            $data->year,
            $data->startDate,
            $data->endDate,
        ));
    }
}
