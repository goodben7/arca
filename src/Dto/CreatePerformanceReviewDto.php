<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreatePerformanceReviewDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 16)]
    #[Assert\Regex(pattern: '/^EM/', message: 'employee must be an Employee id (EM...)')]
    public string $employee;

    #[Assert\NotBlank]
    #[Assert\Length(max: 16)]
    #[Assert\Regex(pattern: '/^EC/', message: 'evaluationCycleId must be an EvaluationCycle id (EC...)')]
    public string $evaluationCycleId;

    #[Assert\Length(max: 16)]
    public ?string $reviewer = null;

    #[Assert\Range(min: 0, max: 5)]
    public ?float $score = null;

    public ?string $comment = null;
}
