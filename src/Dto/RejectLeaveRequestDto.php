<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class RejectLeaveRequestDto
{
    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    public string $leaveRequestId;

    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    public string $raison;
}