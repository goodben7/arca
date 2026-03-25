<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\RejectApplicationDto;
use App\Entity\Application;
use App\Manager\ApplicationManager;
use App\Model\RejectApplicationModel;

class RejectApplicationProcessor implements ProcessorInterface
{
    public function __construct(private ApplicationManager $manager)
    {
    }

    /**
     * @param RejectApplicationDto $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Application
    {
        return $this->manager->rejectFrom(new RejectApplicationModel($data->applicationId, $data->reason));
    }
}

