<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class ValidateEmployeeSkillModel
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 16)]
        #[Assert\Regex(pattern: '/^ES/', message: 'employeeSkillId must be an EmployeeSkill id (ES...)')]
        public ?string $employeeSkillId,
    ) {
    }
}
