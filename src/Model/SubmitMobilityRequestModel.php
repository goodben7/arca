<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class SubmitMobilityRequestModel
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^MB/')]
        public ?string $mobilityRequestId,
    ) {
    }
}
