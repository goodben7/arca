<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class MarkTrainingEnrollmentAbsentDto
{
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public string $trainingEnrollmentId;
}

