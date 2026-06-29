<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\RejectMobilityRequestDto;
use App\Entity\MobilityRequest;
use App\Manager\MobilityRequestManager;

class RejectMobilityRequestProcessor implements ProcessorInterface
{
    public function __construct(private MobilityRequestManager $manager)
    {
    }

    /**
     * @param RejectMobilityRequestDto $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): MobilityRequest
    {
        return $this->manager->reject($data->mobilityRequestId, $data->reason);
    }
}
