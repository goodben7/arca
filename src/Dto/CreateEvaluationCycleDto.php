<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateEvaluationCycleDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    public string $name;

    #[Assert\NotNull]
    #[Assert\Range(min: 2000, max: 2100)]
    public int $year;

    #[Assert\NotNull]
    public \DateTimeImmutable $startDate;

    #[Assert\NotNull]
    public \DateTimeImmutable $endDate;
}
