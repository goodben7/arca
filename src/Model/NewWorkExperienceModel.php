<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class NewWorkExperienceModel
{
    public function __construct(
        #[Assert\NotBlank]
        public ?string $employeeId,

        #[Assert\NotBlank]
        public ?string $company,

        #[Assert\NotBlank]
        public ?string $position,

        #[Assert\NotNull]
        public ?\DateTimeInterface $startDate,

        public ?\DateTimeInterface $endDate = null,
        public ?string $description = null,
        public ?bool $isInternal = null,
    ) {
    }
}
