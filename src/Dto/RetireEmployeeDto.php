<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class RetireEmployeeDto
{
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public string $employeeId;
}
