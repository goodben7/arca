<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class NewTrainingRequestModel
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 16)]
        #[Assert\Regex(pattern: '/^DP/', message: 'department must be a Department id (DP...)')]
        public ?string $department,

        #[Assert\NotBlank]
        public ?string $title,

        #[Assert\NotBlank]
        public ?string $description,

        #[Assert\Positive]
        #[Assert\NotNull]
        public ?int $numberOfParticipants,

        #[Assert\NotBlank]
        public ?string $priority,
    ) {
    }
}

