<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CancelDisciplinaryCaseDto;
use App\Entity\DisciplinaryCase;
use App\Manager\DisciplinaryCaseManager;
use App\Model\CancelDisciplinaryCaseModel;

class CancelDisciplinaryCaseProcessor implements ProcessorInterface
{
    public function __construct(private DisciplinaryCaseManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): DisciplinaryCase
    {
        /** @var CancelDisciplinaryCaseDto $data */
        return $this->manager->cancelFrom(new CancelDisciplinaryCaseModel($data->disciplinaryCaseId));
    }
}
