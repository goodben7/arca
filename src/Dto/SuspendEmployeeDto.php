<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class SuspendEmployeeDto
{
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public string $employeeId;
}
