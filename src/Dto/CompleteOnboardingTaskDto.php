<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CompleteOnboardingTaskDto
{
    #[Assert\NotBlank]
    public string $onboardingTaskId;
}
