<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class AssignManagerEmployeeDto
{
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public string $employeeId;

    #[Assert\NotNull]
    #[Assert\NotBlank]
    public string $managerId;
}
