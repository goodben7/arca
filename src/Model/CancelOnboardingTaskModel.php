<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class CancelOnboardingTaskModel
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 16)]
        #[Assert\Regex(pattern: '/^OT/', message: 'onboardingTaskId must be an OnboardingTask id (OT...)')]
        public ?string $onboardingTaskId,
    ) {
    }
}
