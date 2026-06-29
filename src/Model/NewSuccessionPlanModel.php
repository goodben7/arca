<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class NewSuccessionPlanModel
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^JR/')]
        public ?string $criticalJobRoleId,
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^EM/')]
        public ?string $candidate,
        #[Assert\NotBlank]
        public ?string $readinessLevel,
        public ?string $notes = null,
    ) {
    }
}
