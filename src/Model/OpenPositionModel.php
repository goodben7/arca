<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class OpenPositionModel
{
    public function __construct(
        #[Assert\NotBlank]
        public ?string $positionId,
    ) {
    }
}
