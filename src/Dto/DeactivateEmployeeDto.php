<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class DeactivateEmployeeDto
{
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public string $employeeId;
}
