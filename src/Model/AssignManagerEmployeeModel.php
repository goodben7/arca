<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class AssignManagerEmployeeModel
{
    public function __construct(
        #[Assert\NotBlank]
        public ?string $employeeId,

        #[Assert\NotBlank]
        public ?string $managerId,
    ) {
    }
}
