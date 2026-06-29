<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateExitProcessDto
{
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^EM/')]
    public string $employee;

    #[Assert\NotBlank]
    public string $reason;

    #[Assert\NotNull]
    public \DateTimeImmutable $departureDate;
}
