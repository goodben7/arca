<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class ActivateEmployeeModel
{
    public function __construct(
        #[Assert\NotBlank]
        public ?string $employeeId,
    ) {
    }
}
