<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CertifyTrainingEnrollmentDto;
use App\Entity\TrainingEnrollment;
use App\Manager\TrainingEnrollmentManager;
use App\Model\CertifyTrainingEnrollmentModel;

class CertifyTrainingEnrollmentProcessor implements ProcessorInterface
{
    public function __construct(private TrainingEnrollmentManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TrainingEnrollment
    {
        /** @var CertifyTrainingEnrollmentDto $data */
        return $this->manager->certifyFrom(new CertifyTrainingEnrollmentModel(
            $data->trainingEnrollmentId,
            $data->score,
            $data->certificate,
        ));
    }
}
