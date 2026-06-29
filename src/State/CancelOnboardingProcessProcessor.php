<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CancelOnboardingProcessDto;
use App\Entity\OnboardingProcess;
use App\Manager\OnboardingProcessManager;
use App\Model\CancelOnboardingProcessModel;

class CancelOnboardingProcessProcessor implements ProcessorInterface
{
    public function __construct(private OnboardingProcessManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): OnboardingProcess
    {
        /** @var CancelOnboardingProcessDto $data */
        return $this->manager->cancelFrom(new CancelOnboardingProcessModel($data->onboardingProcessId));
    }
}
