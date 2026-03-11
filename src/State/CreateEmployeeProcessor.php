<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Employee;
use App\Manager\EmployeeManager;
use App\Model\NewEmployeeModel;

class CreateEmployeeProcessor implements ProcessorInterface
{
    public function __construct(
        private EmployeeManager $manager,
    ) {
    }

    /**
     * @param \App\Dto\CreateEmployeeDto $data 
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Employee
    {
        
        $model = new NewEmployeeModel(
            $data->firstName,
            $data->lastName,
            $data->gender,
            $data->hireDate,
            $data->email,
            $data->phone,
            $data->birthDate,
            $data->nationality,
            $data->maritalStatus,
            $data->departureDate,
            $data->department,
            $data->position,
            $data->employeeNumber,
            $data->managerId,
            $data->profile,
        );

        $employee = $this->manager->createFrom($model);

        return $employee;
    }
}
