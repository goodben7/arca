<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\DecideDisciplinaryCaseDto;
use App\Entity\DisciplinaryCase;
use App\Manager\DisciplinaryCaseManager;
use App\Model\DecideDisciplinaryCaseModel;

class DecideDisciplinaryCaseProcessor implements ProcessorInterface
{
    public function __construct(private DisciplinaryCaseManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): DisciplinaryCase
    {
        /** @var DecideDisciplinaryCaseDto $data */
        return $this->manager->decideFrom(new DecideDisciplinaryCaseModel(
            $data->disciplinaryCaseId,
            $data->reason,
        ));
    }
}
