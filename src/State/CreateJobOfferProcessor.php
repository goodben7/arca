<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CreateJobOfferDto;
use App\Entity\JobOffer;
use App\Manager\JobOfferManager;
use App\Model\NewJobOfferModel;

class CreateJobOfferProcessor implements ProcessorInterface
{
    public function __construct(private JobOfferManager $manager)
    {
    }

    /**
     * @param CreateJobOfferDto $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): JobOffer
    {
        $model = new NewJobOfferModel(
            $data->title,
            $data->description,
            $data->department,
            $data->recruitmentRequest,
        );

        return $this->manager->createFrom($model);
    }
}
