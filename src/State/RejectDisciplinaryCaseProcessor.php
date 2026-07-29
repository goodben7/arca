<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\RejectDisciplinaryCaseDto;
use App\Entity\DisciplinaryCase;
use App\Manager\DisciplinaryCaseManager;
use App\Model\RejectDisciplinaryCaseModel;

class RejectDisciplinaryCaseProcessor implements ProcessorInterface
{
    public function __construct(private DisciplinaryCaseManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): DisciplinaryCase
    {
        /** @var RejectDisciplinaryCaseDto $data */
        return $this->manager->rejectFrom(new RejectDisciplinaryCaseModel(
            $data->disciplinaryCaseId,
            $data->reason,
        ));
    }
}
