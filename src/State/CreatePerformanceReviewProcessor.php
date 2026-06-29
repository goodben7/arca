<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CreatePerformanceReviewDto;
use App\Entity\PerformanceReview;
use App\Manager\PerformanceReviewManager;
use App\Model\NewPerformanceReviewModel;

class CreatePerformanceReviewProcessor implements ProcessorInterface
{
    public function __construct(private PerformanceReviewManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): PerformanceReview
    {
        /** @var CreatePerformanceReviewDto $data */
        return $this->manager->createFrom(new NewPerformanceReviewModel(
            $data->employee,
            $data->evaluationCycleId,
            $data->reviewer,
            $data->score,
            $data->comment,
        ));
    }
}
