<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class RejectMobilityRequestDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 16)]
    #[Assert\Regex(pattern: '/^MB/', message: 'mobilityRequestId must be a MobilityRequest id (MB...)')]
    public string $mobilityRequestId;

    #[Assert\NotBlank]
    public string $reason;
}
