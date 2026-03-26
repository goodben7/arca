<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateTrainingEnrollmentDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 16)]
    #[Assert\Regex(pattern: '/^EM/', message: 'employee must be an Employee id (EM...)')]
    public string $employee;

    #[Assert\NotBlank]
    #[Assert\Length(max: 16)]
    #[Assert\Regex(pattern: '/^TS/', message: 'trainingSession must be a TrainingSession id (TS...)')]
    public string $trainingSession;
}

