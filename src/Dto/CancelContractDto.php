<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CancelContractDto
{
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public string $contractId;
}
