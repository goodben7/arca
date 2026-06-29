<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CompleteOnboardingProcessDto;
use App\Entity\OnboardingProcess;
use App\Manager\OnboardingProcessManager;
use App\Model\CompleteOnboardingProcessModel;

class CompleteOnboardingProcessProcessor implements ProcessorInterface
{
    public function __construct(private OnboardingProcessManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): OnboardingProcess
    {
        /** @var CompleteOnboardingProcessDto $data */
        return $this->manager->completeFrom(new CompleteOnboardingProcessModel($data->onboardingProcessId));
    }
}
