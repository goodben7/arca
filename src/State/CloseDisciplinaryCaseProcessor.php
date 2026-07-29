<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CloseDisciplinaryCaseDto;
use App\Entity\DisciplinaryCase;
use App\Manager\DisciplinaryCaseManager;
use App\Model\CloseDisciplinaryCaseModel;

class CloseDisciplinaryCaseProcessor implements ProcessorInterface
{
    public function __construct(private DisciplinaryCaseManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): DisciplinaryCase
    {
        /** @var CloseDisciplinaryCaseDto $data */
        return $this->manager->closeFrom(new CloseDisciplinaryCaseModel($data->disciplinaryCaseId));
    }
}
