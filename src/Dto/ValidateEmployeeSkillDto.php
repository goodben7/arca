<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ValidateEmployeeSkillDto
{
    #[Assert\NotBlank]
    public string $employeeSkillId;
}
