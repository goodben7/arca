<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\OpenDisciplinaryCaseDto;
use App\Entity\DisciplinaryCase;
use App\Manager\DisciplinaryCaseManager;
use App\Model\OpenDisciplinaryCaseModel;

class OpenDisciplinaryCaseProcessor implements ProcessorInterface
{
    public function __construct(private DisciplinaryCaseManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): DisciplinaryCase
    {
        /** @var OpenDisciplinaryCaseDto $data */
        return $this->manager->openFrom(new OpenDisciplinaryCaseModel($data->disciplinaryCaseId));
    }
}
