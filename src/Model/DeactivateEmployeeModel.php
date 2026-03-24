<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class DeactivateEmployeeModel
{
    public function __construct(
        #[Assert\NotBlank]
        public ?string $employeeId,
    ) {
    }
}
