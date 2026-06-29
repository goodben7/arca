<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CancelOnboardingTaskDto
{
    #[Assert\NotBlank]
    public string $onboardingTaskId;
}
