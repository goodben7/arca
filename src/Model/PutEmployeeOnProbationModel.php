<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class PutEmployeeOnProbationModel
{
    public function __construct(
        #[Assert\NotBlank]
        public ?string $employeeId,
    ) {
    }
}
