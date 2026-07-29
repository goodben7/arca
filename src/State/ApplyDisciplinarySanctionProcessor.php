<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\ApplyDisciplinarySanctionDto;
use App\Entity\DisciplinaryCase;
use App\Manager\DisciplinaryCaseManager;
use App\Model\ApplyDisciplinarySanctionModel;

class ApplyDisciplinarySanctionProcessor implements ProcessorInterface
{
    public function __construct(private DisciplinaryCaseManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): DisciplinaryCase
    {
        /** @var ApplyDisciplinarySanctionDto $data */
        return $this->manager->applyFrom(new ApplyDisciplinarySanctionModel(
            $data->disciplinaryCaseId,
            $data->file,
        ));
    }
}
