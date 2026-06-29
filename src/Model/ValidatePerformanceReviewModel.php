<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class ValidatePerformanceReviewModel
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^PV/')]
        public ?string $performanceReviewId,
    ) {
    }
}
