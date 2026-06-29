<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class NewEvaluationCycleModel
{
    public function __construct(
        #[Assert\NotBlank]
        public ?string $name,
        #[Assert\NotNull]
        public ?int $year,
        #[Assert\NotNull]
        public ?\DateTimeImmutable $startDate,
        #[Assert\NotNull]
        public ?\DateTimeImmutable $endDate,
    ) {
    }
}
