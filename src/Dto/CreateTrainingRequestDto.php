<?php

namespace App\Dto;

use App\Model\TrainingRequestConstants;
use Symfony\Component\Validator\Constraints as Assert;

class CreateTrainingRequestDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 16)]
    #[Assert\Regex(pattern: '/^DP/', message: 'department must be a Department id (DP...)')]
    public string $department;

    #[Assert\NotBlank]
    public string $title;

    #[Assert\NotBlank]
    public string $description;

    #[Assert\Positive]
    #[Assert\NotNull]
    public int $numberOfParticipants;

    #[Assert\NotBlank]
    #[Assert\Choice(callback: [TrainingRequestConstants::class, 'getPriorities'])]
    public string $priority;
}
