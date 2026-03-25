<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class RejectApplicationDto
{
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public string $applicationId;

    public ?string $reason = null;
}
