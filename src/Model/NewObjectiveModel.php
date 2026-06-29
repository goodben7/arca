<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class NewObjectiveModel
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^EM/')]
        public ?string $employee,
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^EC/')]
        public ?string $evaluationCycleId,
        #[Assert\NotBlank]
        public ?string $title,
        public ?string $description,
        #[Assert\NotBlank]
        public ?string $specific,
        #[Assert\NotBlank]
        public ?string $measurable,
        public ?string $targetValue,
        public ?string $achievable,
        public ?string $relevant,
        #[Assert\NotNull]
        public ?\DateTimeImmutable $dueDate,
    ) {
    }
}
