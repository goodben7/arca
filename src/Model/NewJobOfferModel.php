<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class NewJobOfferModel
{
    public function __construct(
        #[Assert\NotBlank]
        public ?string $title,

        #[Assert\NotBlank]
        #[Assert\Length(max: 16)]
        #[Assert\Regex(pattern: '/^DP/', message: 'department must be a Department id (DP...)')]
        public ?string $department,

        #[Assert\NotBlank]
        #[Assert\Length(max: 16)]
        #[Assert\Regex(pattern: '/^RR/', message: 'recruitmentRequest must be a RecruitmentRequest id (RR...)')]
        public ?string $recruitmentRequest,
    ) {
    }
}

