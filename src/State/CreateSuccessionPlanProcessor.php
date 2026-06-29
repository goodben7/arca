<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CreateSuccessionPlanDto;
use App\Entity\SuccessionPlan;
use App\Manager\SuccessionPlanManager;
use App\Model\NewSuccessionPlanModel;

class CreateSuccessionPlanProcessor implements ProcessorInterface
{
    public function __construct(private SuccessionPlanManager $manager)
    {
    }

    /**
     * @param CreateSuccessionPlanDto $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): SuccessionPlan
    {
        return $this->manager->createFrom(new NewSuccessionPlanModel(
            $data->criticalJobRoleId,
            $data->candidate,
            $data->readinessLevel,
            $data->notes,
        ));
    }
}
