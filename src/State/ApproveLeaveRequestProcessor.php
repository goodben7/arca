<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\LeaveRequest;
use App\Manager\LeaveRequestManager;

class ApproveLeaveRequestProcessor implements ProcessorInterface
{
    public function __construct(private LeaveRequestManager $manager)
    {
    }

    /**
     * @param \App\Dto\ApproveLeaveRequestDto $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): LeaveRequest
    {
        return $this->manager->approve($data->leaveRequestId);
    }
}
