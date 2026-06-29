<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class StartExitProcessDto
{
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^EP/')]
    public string $exitProcessId;
}
