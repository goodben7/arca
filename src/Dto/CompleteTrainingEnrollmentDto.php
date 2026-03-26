<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CompleteTrainingEnrollmentDto
{
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public string $trainingEnrollmentId;
}

