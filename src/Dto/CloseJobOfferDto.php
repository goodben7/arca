<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CloseJobOfferDto
{
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public string $jobOfferId;
}
