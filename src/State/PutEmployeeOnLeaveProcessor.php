<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Employee;
use App\Manager\EmployeeManager;
use App\Model\PutEmployeeOnLeaveModel;

class PutEmployeeOnLeaveProcessor implements ProcessorInterface
{
    public function __construct(private EmployeeManager $manager)
    {
    }

    /**
     * @param \App\Dto\PutEmployeeOnLeaveDto $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Employee
    {
        return $this->manager->putOnLeaveFrom(new PutEmployeeOnLeaveModel($data->employeeId));
    }
}
