<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateJobOfferDto
{
    #[Assert\NotBlank]
    public string $title;

    #[Assert\NotBlank]
    public string $description;

    #[Assert\NotBlank]
    #[Assert\Length(max: 16)]
    #[Assert\Regex(pattern: '/^DP/', message: 'department must be a Department id (DP...)')]
    public string $department;

    #[Assert\Length(max: 16)]
    #[Assert\Regex(pattern: '/^RR/', message: 'recruitmentRequest must be a RecruitmentRequest id (RR...)')]
    public ?string $recruitmentRequest = null;
}
