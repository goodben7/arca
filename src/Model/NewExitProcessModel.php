<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class NewExitProcessModel
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^EM/')]
        public ?string $employee,
        #[Assert\NotBlank]
        public ?string $reason,
        #[Assert\NotNull]
        public ?\DateTimeImmutable $departureDate,
    ) {
    }
}
