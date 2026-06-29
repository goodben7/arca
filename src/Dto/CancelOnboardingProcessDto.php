<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CancelOnboardingProcessDto
{
    #[Assert\NotBlank]
    public string $onboardingProcessId;
}
