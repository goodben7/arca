<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ScheduleDisciplinaryHearingDto
{
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^DS/')]
    public string $disciplinaryCaseId;

    #[Assert\NotNull]
    public \DateTimeInterface $hearingAt;
}
