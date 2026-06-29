<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\StartTrainingEnrollmentDto;
use App\Entity\TrainingEnrollment;
use App\Manager\TrainingEnrollmentManager;

class StartTrainingEnrollmentProcessor implements ProcessorInterface
{
    public function __construct(private TrainingEnrollmentManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TrainingEnrollment
    {
        /** @var StartTrainingEnrollmentDto $data */
        return $this->manager->start($data->trainingEnrollmentId);
    }
}
