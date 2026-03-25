<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\ShortlistApplicationDto;
use App\Entity\Application;
use App\Manager\ApplicationManager;
use App\Model\ShortlistApplicationModel;

class ShortlistApplicationProcessor implements ProcessorInterface
{
    public function __construct(private ApplicationManager $manager)
    {
    }

    /**
     * @param ShortlistApplicationDto $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Application
    {
        return $this->manager->shortlistFrom(new ShortlistApplicationModel($data->applicationId));
    }
}

