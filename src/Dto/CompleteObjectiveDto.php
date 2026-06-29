<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CompleteObjectiveDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 16)]
    #[Assert\Regex(pattern: '/^OB/', message: 'objectiveId must be an Objective id (OB...)')]
    public string $objectiveId;
}
