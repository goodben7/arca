<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ClosePositionDto
{
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public string $positionId;
}
