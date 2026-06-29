<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class CertifyTrainingEnrollmentModel
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^TE/')]
        public ?string $trainingEnrollmentId,
        public ?float $score = null,
        public ?string $certificate = null,
    ) {
    }
}
