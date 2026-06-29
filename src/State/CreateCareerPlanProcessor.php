<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CreateCareerPlanDto;
use App\Entity\CareerPlan;
use App\Manager\CareerPlanManager;
use App\Model\NewCareerPlanModel;

class CreateCareerPlanProcessor implements ProcessorInterface
{
    public function __construct(private CareerPlanManager $manager)
    {
    }

    /**
     * @param CreateCareerPlanDto $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): CareerPlan
    {
        $model = new NewCareerPlanModel(
            $data->employee,
            $data->targetJobRoleId,
            $data->targetDate,
            $data->targetGradeId,
            $data->notes,
        );

        return $this->manager->createFrom($model);
    }
}
