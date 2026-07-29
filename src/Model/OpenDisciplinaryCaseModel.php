<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class OpenDisciplinaryCaseModel
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^DS/')]
        public ?string $disciplinaryCaseId,
    ) {
    }
}
