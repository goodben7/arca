<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class RejectRecruitmentRequestDto
{
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public string $recruitmentRequestId;

    #[Assert\NotNull]
    #[Assert\NotBlank]
    public string $reason;
}
