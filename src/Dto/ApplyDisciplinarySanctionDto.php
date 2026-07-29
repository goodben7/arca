<?php

namespace App\Dto;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

class ApplyDisciplinarySanctionDto
{
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^DS/')]
    public string $disciplinaryCaseId;

    /** Optional warning letter file (WARN/BLAME only). Use multipart/form-data. */
    #[Assert\File(maxSize: '10M')]
    public ?File $file = null;
}
