<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class SetTrainingEnrollmentEnrolledDto
{
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public string $trainingEnrollmentId;
}

