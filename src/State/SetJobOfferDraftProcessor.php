<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\JobOffer;
use App\Manager\JobOfferManager;
use App\Model\SetJobOfferDraftModel;

class SetJobOfferDraftProcessor implements ProcessorInterface
{
    public function __construct(private JobOfferManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): JobOffer
    {
        return $this->manager->setDraftFrom(new SetJobOfferDraftModel($data->jobOfferId));
    }
}

