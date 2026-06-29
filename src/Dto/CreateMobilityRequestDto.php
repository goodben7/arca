<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateMobilityRequestDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 16)]
    #[Assert\Regex(pattern: '/^EM/', message: 'employee must be an Employee id (EM...)')]
    public string $employee;

    #[Assert\NotBlank]
    public string $type;

    #[Assert\Length(max: 16)]
    #[Assert\Regex(pattern: '/^JR/', message: 'targetJobRoleId must be a JobRole id (JR...)')]
    public ?string $targetJobRoleId = null;

    #[Assert\Length(max: 16)]
    #[Assert\Regex(pattern: '/^GR/', message: 'targetGradeId must be a Grade id (GR...)')]
    public ?string $targetGradeId = null;

    #[Assert\Length(max: 120)]
    public ?string $targetDepartment = null;

    public ?string $reason = null;
}
