<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class SetJobOfferDraftDto
{
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public string $jobOfferId;
}
