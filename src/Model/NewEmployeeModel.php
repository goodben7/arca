<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class NewEmployeeModel
{
    public function __construct(
        #[Assert\NotBlank]
        public ?string $firstName,

        #[Assert\NotBlank]
        public ?string $lastName,

        #[Assert\Choice(callback: [EmployeeConstants::class, 'getGenders'])]
        public ?string $gender,

        #[Assert\NotNull]
        public ?\DateTimeInterface $hireDate,

        #[Assert\Email]
        public ?string $email = null,

        public ?string $phone = null,

        public ?\DateTimeInterface $birthDate = null,

        public ?string $nationality = null,

        #[Assert\Choice(callback: [EmployeeConstants::class, 'getMaritalStatuses'])]
        public ?string $maritalStatus = null,

        public ?\DateTimeInterface $departureDate = null,

        public ?string $department = null,

        public ?string $position = null,

        public ?string $employeeNumber = null,

        public ?string $manager = null,
    ) {
    }
}
