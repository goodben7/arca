<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Position;
use App\Manager\PositionManager;
use App\Model\ClosePositionModel;

class ClosePositionProcessor implements ProcessorInterface
{
    public function __construct(private PositionManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Position
    {
        return $this->manager->closeFrom(new ClosePositionModel($data->positionId));
    }
}

