<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class StartExitProcessModel
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^EP/')]
        public ?string $exitProcessId,
    ) {
    }
}
