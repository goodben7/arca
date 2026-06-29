<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\ApproveMobilityRequestDto;
use App\Entity\MobilityRequest;
use App\Manager\MobilityRequestManager;

class ApproveMobilityRequestProcessor implements ProcessorInterface
{
    public function __construct(private MobilityRequestManager $manager)
    {
    }

    /**
     * @param ApproveMobilityRequestDto $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): MobilityRequest
    {
        return $this->manager->approve($data->mobilityRequestId);
    }
}
