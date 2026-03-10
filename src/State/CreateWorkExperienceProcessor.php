<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CreateWorkExperienceDto;
use App\Entity\WorkExperience;
use App\Manager\WorkExperienceManager;
use App\Model\NewWorkExperienceModel;

class CreateWorkExperienceProcessor implements ProcessorInterface
{
    public function __construct(
        private WorkExperienceManager $manager,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): WorkExperience
    {
        /** @var CreateWorkExperienceDto $data */
        $model = new NewWorkExperienceModel(
            $data->employeeId,
            $data->company,
            $data->position,
            $data->startDate,
            $data->endDate,
            $data->description,
            $data->isInternal,
        );

        return $this->manager->createFrom($model);
    }
}
