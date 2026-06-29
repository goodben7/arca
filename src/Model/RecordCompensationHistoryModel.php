<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class RecordCompensationHistoryModel
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^EM/')]
        public ?string $employee,
        #[Assert\NotBlank]
        public ?string $newSalary,
        #[Assert\NotNull]
        public ?\DateTimeImmutable $effectiveDate,
        #[Assert\NotBlank]
        public ?string $reason,
    ) {
    }
}
