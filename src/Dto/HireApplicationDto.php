<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class HireApplicationDto
{
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public string $applicationId;
}
