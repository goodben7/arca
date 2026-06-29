<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class NewPerformanceReviewModel
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^EM/')]
        public ?string $employee,
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^EC/')]
        public ?string $evaluationCycleId,
        public ?string $reviewer = null,
        public ?float $score = null,
        public ?string $comment = null,
    ) {
    }
}
