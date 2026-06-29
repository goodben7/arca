<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CreateEmployeeSkillDto;
use App\Entity\EmployeeSkill;
use App\Manager\EmployeeSkillManager;
use App\Model\NewEmployeeSkillModel;

class CreateEmployeeSkillProcessor implements ProcessorInterface
{
    public function __construct(
        private EmployeeSkillManager $manager,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): EmployeeSkill
    {
        /** @var CreateEmployeeSkillDto $data */
        return $this->manager->assignFrom(new NewEmployeeSkillModel(
            $data->employee,
            $data->skill,
            $data->level,
        ));
    }
}
