<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CloseEvaluationCycleDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 16)]
    #[Assert\Regex(pattern: '/^EC/', message: 'evaluationCycleId must be an EvaluationCycle id (EC...)')]
    public string $evaluationCycleId;
}
