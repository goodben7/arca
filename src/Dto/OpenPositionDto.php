<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class OpenPositionDto
{
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public string $positionId;
}
