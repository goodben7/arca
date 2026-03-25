<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\SetApplicationAppliedDto;
use App\Entity\Application;
use App\Manager\ApplicationManager;
use App\Model\SetApplicationAppliedModel;

class SetApplicationAppliedProcessor implements ProcessorInterface
{
    public function __construct(private ApplicationManager $manager)
    {
    }

    /**
     * @param SetApplicationAppliedDto $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Application
    {
        return $this->manager->setAppliedFrom(new SetApplicationAppliedModel($data->applicationId));
    }
}

