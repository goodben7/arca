<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class NewDisciplinaryCaseModel
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^EM/')]
        public ?string $employee,
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^SS/')]
        public ?string $sanctionScale,
        #[Assert\NotBlank]
        public ?string $facts,
        #[Assert\NotNull]
        public ?\DateTimeInterface $occurredAt,
        public ?string $reason = null,
    ) {
    }
}
