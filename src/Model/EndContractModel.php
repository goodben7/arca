<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class EndContractModel
{
    public function __construct(
        #[Assert\NotBlank]
        public ?string $contractId,
    ) {
    }
}
