<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class RejectTrainingRequestDto
{
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public string $trainingRequestId;

    public ?string $reason = null;
}
