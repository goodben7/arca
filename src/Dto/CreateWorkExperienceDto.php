<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateWorkExperienceDto
{
    #[Assert\NotBlank]
    public string $employeeId;

    #[Assert\NotBlank]
    public string $company;

    #[Assert\NotBlank]
    public string $position;

    #[Assert\NotNull]
    public \DateTimeInterface $startDate;

    public ?\DateTimeInterface $endDate = null;

    public ?string $description = null;

    public ?bool $isInternal = null;
}
