<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class SetApplicationInterviewDto
{
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public string $applicationId;
}
