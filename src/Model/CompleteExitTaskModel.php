<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class CompleteExitTaskModel
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^XT/')]
        public ?string $exitTaskId,
    ) {
    }
}
