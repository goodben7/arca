<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class NewTrainingEnrollmentModel
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 16)]
        #[Assert\Regex(pattern: '/^EM/', message: 'employee must be an Employee id (EM...)')]
        public ?string $employee,

        #[Assert\NotBlank]
        #[Assert\Length(max: 16)]
        #[Assert\Regex(pattern: '/^TS/', message: 'trainingSession must be a TrainingSession id (TS...)')]
        public ?string $trainingSession,
    ) {
    }
}
