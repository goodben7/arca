<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateRecruitmentRequestDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 16)]
    #[Assert\Regex(pattern: '/^DP/', message: 'department must be a Department id (DP...)')]
    public string $department;

    #[Assert\NotBlank]
    #[Assert\Length(max: 16)]
    #[Assert\Regex(pattern: '/^PO/', message: 'position must be a Position id (PO...)')]
    public string $position;

    #[Assert\NotNull]
    #[Assert\Positive]
    public int $numberOfPositions;

    #[Assert\NotBlank]
    public string $justification;

    #[Assert\NotBlank]
    public string $description;
}
