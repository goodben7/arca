<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\TrainingEnrollment;
use App\Manager\TrainingEnrollmentManager;

class SetTrainingEnrollmentEnrolledProcessor implements ProcessorInterface
{
    public function __construct(private TrainingEnrollmentManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TrainingEnrollment
    {
        return $this->manager->setEnrolled($data->trainingEnrollmentId);
    }
}

