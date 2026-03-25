<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CreateRecruitmentRequestDto;
use App\Entity\RecruitmentRequest;
use App\Manager\RecruitmentRequestManager;
use App\Model\NewRecruitmentRequestModel;

class CreateRecruitmentRequestProcessor implements ProcessorInterface
{
    public function __construct(private RecruitmentRequestManager $manager)
    {
    }

    /**
     * @param CreateRecruitmentRequestDto $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): RecruitmentRequest
    {
        $model = new NewRecruitmentRequestModel(
            $data->department,
            $data->position,
            $data->numberOfPositions,
            $data->justification,
        );

        return $this->manager->createFrom($model);
    }
}
