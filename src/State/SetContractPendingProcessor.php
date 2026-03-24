<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Contract;
use App\Manager\ContractManager;
use App\Model\SetContractPendingModel;

class SetContractPendingProcessor implements ProcessorInterface
{
    public function __construct(private ContractManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Contract
    {
        return $this->manager->setPendingFrom(new SetContractPendingModel($data->contractId));
    }
}

