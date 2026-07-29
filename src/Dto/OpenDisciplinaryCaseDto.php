<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class OpenDisciplinaryCaseDto
{
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^DS/')]
    public string $disciplinaryCaseId;
}
