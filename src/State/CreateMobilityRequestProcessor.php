<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CreateMobilityRequestDto;
use App\Entity\MobilityRequest;
use App\Manager\MobilityRequestManager;
use App\Model\NewMobilityRequestModel;

class CreateMobilityRequestProcessor implements ProcessorInterface
{
    public function __construct(private MobilityRequestManager $manager)
    {
    }

    /**
     * @param CreateMobilityRequestDto $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): MobilityRequest
    {
        $model = new NewMobilityRequestModel(
            $data->employee,
            $data->type,
            $data->targetJobRoleId,
            $data->targetGradeId,
            $data->targetDepartment,
            $data->reason,
        );

        return $this->manager->createFrom($model);
    }
}
