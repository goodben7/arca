<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class PublishJobOfferDto
{
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public string $jobOfferId;
}
