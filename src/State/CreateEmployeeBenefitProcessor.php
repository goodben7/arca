<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CreateEmployeeBenefitDto;
use App\Entity\EmployeeBenefit;
use App\Manager\EmployeeBenefitManager;
use App\Model\NewEmployeeBenefitModel;

class CreateEmployeeBenefitProcessor implements ProcessorInterface
{
    public function __construct(private EmployeeBenefitManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): EmployeeBenefit
    {
        /** @var CreateEmployeeBenefitDto $data */
        return $this->manager->createFrom(new NewEmployeeBenefitModel(
            $data->employee,
            $data->benefitId,
            $data->startDate,
            $data->endDate,
        ));
    }
}
