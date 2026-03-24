<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Employee;
use App\Manager\EmployeeManager;
use App\Model\DeactivateEmployeeModel;

class DeactivateEmployeeProcessor implements ProcessorInterface
{
    public function __construct(private EmployeeManager $manager)
    {
    }

    /**
     * @param \App\Dto\DeactivateEmployeeDto $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Employee
    {
        return $this->manager->deactivateFrom(new DeactivateEmployeeModel($data->employeeId));
    }
}
