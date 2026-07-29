<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class RejectDisciplinaryCaseDto
{
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^DS/')]
    public string $disciplinaryCaseId;

    #[Assert\NotBlank]
    public string $reason;
}
