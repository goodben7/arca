<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class NewCareerPlanModel
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^EM/')]
        public ?string $employee,
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^JR/')]
        public ?string $targetJobRoleId,
        #[Assert\NotNull]
        public ?\DateTimeImmutable $targetDate,
        public ?string $targetGradeId = null,
        public ?string $notes = null,
    ) {
    }
}
