<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CompleteOnboardingTaskDto;
use App\Entity\OnboardingTask;
use App\Manager\OnboardingTaskManager;
use App\Model\CompleteOnboardingTaskModel;

class CompleteOnboardingTaskProcessor implements ProcessorInterface
{
    public function __construct(private OnboardingTaskManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): OnboardingTask
    {
        /** @var CompleteOnboardingTaskDto $data */
        return $this->manager->completeFrom(new CompleteOnboardingTaskModel($data->onboardingTaskId));
    }
}
