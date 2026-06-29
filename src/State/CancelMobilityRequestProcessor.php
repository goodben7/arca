<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CancelMobilityRequestDto;
use App\Entity\MobilityRequest;
use App\Manager\MobilityRequestManager;

class CancelMobilityRequestProcessor implements ProcessorInterface
{
    public function __construct(private MobilityRequestManager $manager)
    {
    }

    /**
     * @param CancelMobilityRequestDto $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): MobilityRequest
    {
        return $this->manager->cancel($data->mobilityRequestId);
    }
}
