<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateObjectiveDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 16)]
    #[Assert\Regex(pattern: '/^EM/', message: 'employee must be an Employee id (EM...)')]
    public string $employee;

    #[Assert\NotBlank]
    #[Assert\Length(max: 16)]
    #[Assert\Regex(pattern: '/^EC/', message: 'evaluationCycleId must be an EvaluationCycle id (EC...)')]
    public string $evaluationCycleId;

    #[Assert\NotBlank]
    #[Assert\Length(max: 160)]
    public string $title;

    public ?string $description = null;

    #[Assert\NotBlank]
    public string $specific;

    #[Assert\NotBlank]
    public string $measurable;

    #[Assert\Length(max: 120)]
    public ?string $targetValue = null;

    public ?string $achievable = null;

    public ?string $relevant = null;

    #[Assert\NotNull]
    public \DateTimeImmutable $dueDate;
}
