<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class NewEmployeeSkillModel
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 16)]
        #[Assert\Regex(pattern: '/^EM/', message: 'employee must be an Employee id (EM...)')]
        public ?string $employee,

        #[Assert\NotBlank]
        #[Assert\Length(max: 16)]
        #[Assert\Regex(pattern: '/^SK/', message: 'skill must be a Skill id (SK...)')]
        public ?string $skill,

        #[Assert\NotBlank]
        #[Assert\Choice(callback: [SkillConstants::class, 'getLevels'])]
        public ?string $level,
    ) {
    }
}
