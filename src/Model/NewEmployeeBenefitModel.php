<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class NewEmployeeBenefitModel
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^EM/')]
        public ?string $employee,
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^BF/')]
        public ?string $benefitId,
        #[Assert\NotNull]
        public ?\DateTimeImmutable $startDate,
        public ?\DateTimeImmutable $endDate = null,
    ) {
    }
}
