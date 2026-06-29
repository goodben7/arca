<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class CompleteExitProcessModel
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^EP/')]
        public ?string $exitProcessId,
    ) {
    }
}
