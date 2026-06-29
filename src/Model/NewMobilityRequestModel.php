<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class NewMobilityRequestModel
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^EM/')]
        public ?string $employee,
        #[Assert\NotBlank]
        public ?string $type,
        public ?string $targetJobRoleId = null,
        public ?string $targetGradeId = null,
        public ?string $targetDepartment = null,
        public ?string $reason = null,
    ) {
    }
}
