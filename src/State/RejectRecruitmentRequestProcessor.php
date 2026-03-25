<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\RecruitmentRequest;
use App\Manager\RecruitmentRequestManager;

class RejectRecruitmentRequestProcessor implements ProcessorInterface
{
    public function __construct(private RecruitmentRequestManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): RecruitmentRequest
    {
        return $this->manager->reject($data->recruitmentRequestId, $data->reason);
    }
}

