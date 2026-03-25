<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CreateApplicationDto;
use App\Entity\Application;
use App\Manager\ApplicationManager;
use App\Model\NewApplicationModel;

class CreateApplicationProcessor implements ProcessorInterface
{
    public function __construct(private ApplicationManager $manager)
    {
    }

    /**
     * @param CreateApplicationDto $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Application
    {
        $model = new NewApplicationModel(
            $data->firstName,
            $data->lastName,
            $data->gender,
            $data->email,
            $data->phone,
            $data->jobOffer,
            $data->notes,
        );

        return $this->manager->createFrom($model);
    }
}
