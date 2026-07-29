<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateDisciplinaryCaseDto
{
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^EM/')]
    public string $employee;

    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^SS/')]
    public string $sanctionScale;

    #[Assert\NotBlank]
    public string $facts;

    #[Assert\NotNull]
    public \DateTimeInterface $occurredAt;

    public ?string $reason = null;
}
