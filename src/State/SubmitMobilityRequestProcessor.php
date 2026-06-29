<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\SubmitMobilityRequestDto;
use App\Entity\MobilityRequest;
use App\Manager\MobilityRequestManager;
use App\Model\SubmitMobilityRequestModel;

class SubmitMobilityRequestProcessor implements ProcessorInterface
{
    public function __construct(private MobilityRequestManager $manager)
    {
    }

    /**
     * @param SubmitMobilityRequestDto $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): MobilityRequest
    {
        return $this->manager->submitFrom(new SubmitMobilityRequestModel($data->mobilityRequestId));
    }
}
