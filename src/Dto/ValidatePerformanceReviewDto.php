<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ValidatePerformanceReviewDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 16)]
    #[Assert\Regex(pattern: '/^PV/', message: 'performanceReviewId must be a PerformanceReview id (PV...)')]
    public string $performanceReviewId;
}
