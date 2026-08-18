<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class RequestDisciplinaryExplanationModel
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^DS/')]
        public ?string $disciplinaryCaseId,
        public ?\DateTimeInterface $explanationDueAt = null,
        public ?string $explanationText = null,
    ) {
    }
}
