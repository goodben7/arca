<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class RetireEmployeeModel
{
    public function __construct(
        #[Assert\NotBlank]
        public ?string $employeeId,
    ) {
    }
}
