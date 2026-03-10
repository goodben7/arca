<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ApproveLeaveRequestDto
{
    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    public string $leaveRequestId;
}