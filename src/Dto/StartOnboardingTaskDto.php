<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class StartOnboardingTaskDto
{
    #[Assert\NotBlank]
    public string $onboardingTaskId;
}
