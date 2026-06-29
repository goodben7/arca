<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class ActivateObjectiveModel
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^OB/')]
        public ?string $objectiveId,
    ) {
    }
}
