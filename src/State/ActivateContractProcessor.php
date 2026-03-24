<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Contract;
use App\Manager\ContractManager;
use App\Model\ActivateContractModel;

class ActivateContractProcessor implements ProcessorInterface
{
    public function __construct(private ContractManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Contract
    {
        return $this->manager->activateFrom(new ActivateContractModel($data->contractId));
    }
}

