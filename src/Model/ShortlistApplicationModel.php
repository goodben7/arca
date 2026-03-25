<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class ShortlistApplicationModel
{
    public function __construct(
        #[Assert\NotBlank]
        public ?string $applicationId,
    ) {
    }
}

