<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\RequestDisciplinaryExplanationDto;
use App\Entity\DisciplinaryCase;
use App\Manager\DisciplinaryCaseManager;
use App\Model\RequestDisciplinaryExplanationModel;

class RequestDisciplinaryExplanationProcessor implements ProcessorInterface
{
    public function __construct(private DisciplinaryCaseManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): DisciplinaryCase
    {
        /** @var RequestDisciplinaryExplanationDto $data */
        return $this->manager->requestExplanationFrom(new RequestDisciplinaryExplanationModel(
            $data->disciplinaryCaseId,
            $data->explanationDueAt,
            $data->explanationText,
        ));
    }
}
