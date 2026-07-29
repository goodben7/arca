<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class DecideDisciplinaryCaseDto
{
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^DS/')]
    public string $disciplinaryCaseId;

    public ?string $reason = null;
}
