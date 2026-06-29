<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CertifyTrainingEnrollmentDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 16)]
    #[Assert\Regex(pattern: '/^TE/', message: 'trainingEnrollmentId must be a TrainingEnrollment id (TE...)')]
    public string $trainingEnrollmentId;

    #[Assert\Range(min: 0, max: 100)]
    public ?float $score = null;

    #[Assert\Length(max: 255)]
    public ?string $certificate = null;
}
