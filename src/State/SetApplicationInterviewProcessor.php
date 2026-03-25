<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\SetApplicationInterviewDto;
use App\Entity\Application;
use App\Manager\ApplicationManager;
use App\Model\SetApplicationInterviewModel;

class SetApplicationInterviewProcessor implements ProcessorInterface
{
    public function __construct(private ApplicationManager $manager)
    {
    }

    /**
     * @param SetApplicationInterviewDto $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Application
    {
        return $this->manager->setInterviewFrom(new SetApplicationInterviewModel($data->applicationId));
    }
}

