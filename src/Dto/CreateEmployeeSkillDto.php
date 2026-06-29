<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateEmployeeSkillDto
{
    #[Assert\NotBlank]
    public string $employee;

    #[Assert\NotBlank]
    public string $skill;

    #[Assert\NotBlank]
    #[Assert\Choice(callback: [\App\Model\SkillConstants::class, 'getLevels'])]
    public string $level;
}
