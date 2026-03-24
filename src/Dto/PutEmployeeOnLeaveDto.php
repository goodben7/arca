<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class PutEmployeeOnLeaveDto
{
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public string $employeeId;
}
