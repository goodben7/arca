<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class NewTrainingSessionModel
{
    public function __construct(
        #[Assert\NotBlank]
        public ?string $title,

        #[Assert\NotBlank]
        public ?string $trainer,

        #[Assert\NotNull]
        public ?\DateTimeInterface $startDate,

        #[Assert\NotNull]
        #[Assert\GreaterThan(propertyPath: 'startDate')]
        public ?\DateTimeInterface $endDate,

        #[Assert\NotBlank]
        public ?string $location,

        #[Assert\Positive]
        #[Assert\NotNull]
        public ?int $capacity,

        #[Assert\NotBlank]
        #[Assert\Length(max: 16)]
        #[Assert\Regex(pattern: '/^TR/', message: 'trainingRequest must be a TrainingRequest id (TR...)')]
        public ?string $trainingRequest,

        #[Assert\Length(max: 16)]
        #[Assert\Regex(pattern: '/^TC/', message: 'catalogItem must be a TrainingCatalog id (TC...)')]
        public ?string $catalogItem = null,
    ) {
    }
}

