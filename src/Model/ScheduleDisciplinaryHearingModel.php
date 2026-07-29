<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class ScheduleDisciplinaryHearingModel
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^DS/')]
        public ?string $disciplinaryCaseId,
        #[Assert\NotNull]
        public ?\DateTimeInterface $hearingAt,
    ) {
    }
}
