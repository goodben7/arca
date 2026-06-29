<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class SubmitPerformanceReviewModel
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^PV/')]
        public ?string $performanceReviewId,
    ) {
    }
}
