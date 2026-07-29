<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class DecideDisciplinaryCaseModel
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^DS/')]
        public ?string $disciplinaryCaseId,
        public ?string $reason = null,
    ) {
    }
}
