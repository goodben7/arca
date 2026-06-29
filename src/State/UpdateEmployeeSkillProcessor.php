<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\EmployeeSkill;
use App\Manager\EmployeeSkillManager;

class UpdateEmployeeSkillProcessor implements ProcessorInterface
{
    public function __construct(
        private EmployeeSkillManager $manager,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): EmployeeSkill
    {
        /** @var EmployeeSkill $data */
        return $this->manager->applyUpdate($data);
    }
}
