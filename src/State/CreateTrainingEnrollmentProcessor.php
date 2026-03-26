<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CreateTrainingEnrollmentDto;
use App\Entity\TrainingEnrollment;
use App\Manager\TrainingEnrollmentManager;
use App\Model\NewTrainingEnrollmentModel;

class CreateTrainingEnrollmentProcessor implements ProcessorInterface
{
    public function __construct(private TrainingEnrollmentManager $manager)
    {
    }

    /**
     * @param CreateTrainingEnrollmentDto $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TrainingEnrollment
    {
        $model = new NewTrainingEnrollmentModel(
            $data->employee,
            $data->trainingSession,
        );

        return $this->manager->createFrom($model);
    }
}

