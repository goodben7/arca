<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class CancelOnboardingProcessModel
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 16)]
        #[Assert\Regex(pattern: '/^OP/', message: 'onboardingProcessId must be an OnboardingProcess id (OP...)')]
        public ?string $onboardingProcessId,
    ) {
    }
}
