<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CreateDisciplinaryCaseDto;
use App\Entity\DisciplinaryCase;
use App\Manager\DisciplinaryCaseManager;
use App\Model\NewDisciplinaryCaseModel;

class CreateDisciplinaryCaseProcessor implements ProcessorInterface
{
    public function __construct(private DisciplinaryCaseManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): DisciplinaryCase
    {
        /** @var CreateDisciplinaryCaseDto $data */
        return $this->manager->createFrom(new NewDisciplinaryCaseModel(
            $data->employee,
            $data->sanctionScale,
            $data->facts,
            $data->occurredAt,
            $data->reason,
        ));
    }
}
