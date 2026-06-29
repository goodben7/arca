<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\ValidateEmployeeSkillDto;
use App\Entity\EmployeeSkill;
use App\Manager\EmployeeSkillManager;
use App\Model\ValidateEmployeeSkillModel;

class ValidateEmployeeSkillProcessor implements ProcessorInterface
{
    public function __construct(
        private EmployeeSkillManager $manager,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): EmployeeSkill
    {
        /** @var ValidateEmployeeSkillDto $data */
        return $this->manager->validateFrom(new ValidateEmployeeSkillModel($data->employeeSkillId));
    }
}
