<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class CancelDisciplinaryCaseModel
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^DS/')]
        public ?string $disciplinaryCaseId,
    ) {
    }
}
