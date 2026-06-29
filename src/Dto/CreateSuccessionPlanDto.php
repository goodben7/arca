<?php

namespace App\Dto;

use App\Model\SuccessionPlanConstants;
use Symfony\Component\Validator\Constraints as Assert;

class CreateSuccessionPlanDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 16)]
    #[Assert\Regex(pattern: '/^JR/', message: 'criticalJobRoleId must be a JobRole id (JR...)')]
    public string $criticalJobRoleId;

    #[Assert\NotBlank]
    #[Assert\Length(max: 16)]
    #[Assert\Regex(pattern: '/^EM/', message: 'candidate must be an Employee id (EM...)')]
    public string $candidate;

    #[Assert\NotBlank]
    #[Assert\Choice(callback: [SuccessionPlanConstants::class, 'getReadinessLevels'])]
    public string $readinessLevel;

    public ?string $notes = null;
}
