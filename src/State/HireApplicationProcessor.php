<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Application;
use App\Manager\ApplicationManager;
use App\Model\HireApplicationModel;

class HireApplicationProcessor implements ProcessorInterface
{
    public function __construct(private ApplicationManager $manager)
    {
    }

    /**
     * @param HireApplicationModel $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Application
    {
        return $this->manager->hireFrom(new HireApplicationModel($data->applicationId));
    }
}

