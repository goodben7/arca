<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\RecruitmentRequest;
use App\Manager\RecruitmentRequestManager;

class ApproveRecruitmentRequestProcessor implements ProcessorInterface
{
    public function __construct(private RecruitmentRequestManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): RecruitmentRequest
    {
        return $this->manager->approve($data->recruitmentRequestId);
    }
}

