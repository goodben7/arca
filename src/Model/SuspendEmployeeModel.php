<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class SuspendEmployeeModel
{
    public function __construct(
        #[Assert\NotBlank]
        public ?string $employeeId,
    ) {
    }
}
