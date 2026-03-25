<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ApproveRecruitmentRequestDto
{
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public string $recruitmentRequestId;
}
