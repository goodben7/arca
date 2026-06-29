<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\SubmitPerformanceReviewDto;
use App\Entity\PerformanceReview;
use App\Manager\PerformanceReviewManager;
use App\Model\SubmitPerformanceReviewModel;

class SubmitPerformanceReviewProcessor implements ProcessorInterface
{
    public function __construct(private PerformanceReviewManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): PerformanceReview
    {
        /** @var SubmitPerformanceReviewDto $data */
        return $this->manager->submitFrom(new SubmitPerformanceReviewModel($data->performanceReviewId));
    }
}
