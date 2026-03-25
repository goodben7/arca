<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class SetJobOfferDraftModel
{
    public function __construct(
        #[Assert\NotBlank]
        public ?string $jobOfferId,
    ) {
    }
}
