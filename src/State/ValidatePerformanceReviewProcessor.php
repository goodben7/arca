<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\ValidatePerformanceReviewDto;
use App\Entity\PerformanceReview;
use App\Manager\PerformanceReviewManager;
use App\Model\ValidatePerformanceReviewModel;

class ValidatePerformanceReviewProcessor implements ProcessorInterface
{
    public function __construct(private PerformanceReviewManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): PerformanceReview
    {
        /** @var ValidatePerformanceReviewDto $data */
        return $this->manager->validateFrom(new ValidatePerformanceReviewModel($data->performanceReviewId));
    }
}
