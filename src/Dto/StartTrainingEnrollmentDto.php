<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class StartTrainingEnrollmentDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 16)]
    #[Assert\Regex(pattern: '/^TE/', message: 'trainingEnrollmentId must be a TrainingEnrollment id (TE...)')]
    public string $trainingEnrollmentId;
}
