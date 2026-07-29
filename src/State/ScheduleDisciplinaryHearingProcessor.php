<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\ScheduleDisciplinaryHearingDto;
use App\Entity\DisciplinaryCase;
use App\Manager\DisciplinaryCaseManager;
use App\Model\ScheduleDisciplinaryHearingModel;

class ScheduleDisciplinaryHearingProcessor implements ProcessorInterface
{
    public function __construct(private DisciplinaryCaseManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): DisciplinaryCase
    {
        /** @var ScheduleDisciplinaryHearingDto $data */
        return $this->manager->scheduleHearingFrom(new ScheduleDisciplinaryHearingModel(
            $data->disciplinaryCaseId,
            $data->hearingAt,
        ));
    }
}
