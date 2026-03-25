<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class RejectApplicationModel
{
    public function __construct(
        #[Assert\NotBlank]
        public ?string $applicationId,

        public ?string $reason = null,
    ) {
    }
}

