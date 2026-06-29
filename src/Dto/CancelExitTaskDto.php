<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CancelExitTaskDto
{
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^XT/')]
    public string $exitTaskId;
}
