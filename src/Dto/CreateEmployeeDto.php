<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;
use App\Model\EmployeeConstants;

class CreateEmployeeDto
{
    #[Assert\NotBlank]
    public string $firstName;

    #[Assert\NotBlank]
    public string $lastName;

    #[Assert\Email]
    public ?string $email = null;

    public ?string $phone = null;

    #[Assert\Choice(callback: [EmployeeConstants::class, 'getGenders'])]
    public string $gender;

    public ?\DateTimeInterface $birthDate = null;

    public ?string $nationality = null;

    #[Assert\Choice(callback: [EmployeeConstants::class, 'getMaritalStatuses'])]
    public ?string $maritalStatus = null;

    #[Assert\NotNull]
    public \DateTimeInterface $hireDate;

    public ?\DateTimeInterface $departureDate = null;

    public ?string $department = null;

    public ?string $position = null;

    public ?string $employeeNumber = null;

    public ?string $managerId = null;
}
