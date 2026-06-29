<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateEmployeeBenefitDto
{
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^EM/')]
    public string $employee;

    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^BF/')]
    public string $benefitId;

    #[Assert\NotNull]
    public \DateTimeImmutable $startDate;

    public ?\DateTimeImmutable $endDate = null;
}
