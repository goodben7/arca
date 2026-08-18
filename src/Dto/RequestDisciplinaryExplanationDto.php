<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class RequestDisciplinaryExplanationDto
{
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^DS/')]
    public string $disciplinaryCaseId;

    public ?\DateTimeInterface $explanationDueAt = null;

    public ?string $explanationText = null;
}
