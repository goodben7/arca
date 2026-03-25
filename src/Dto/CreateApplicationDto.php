<?php

namespace App\Dto;

use App\Model\EmployeeConstants;
use Symfony\Component\Validator\Constraints as Assert;

class CreateApplicationDto
{
    #[Assert\NotBlank]
    public string $firstName;

    #[Assert\NotBlank]
    public string $lastName;

    #[Assert\Choice(callback: [EmployeeConstants::class, 'getGenders'])]
    public ?string $gender = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;

    #[Assert\NotBlank]
    public string $phone;

    #[Assert\NotBlank]
    #[Assert\Length(max: 16)]
    #[Assert\Regex(pattern: '/^JO/', message: 'jobOffer must be a JobOffer id (JO...)')]
    public string $jobOffer;

    public ?string $notes = null;
}
