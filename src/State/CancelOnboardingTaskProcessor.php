<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CancelOnboardingTaskDto;
use App\Entity\OnboardingTask;
use App\Manager\OnboardingTaskManager;
use App\Model\CancelOnboardingTaskModel;

class CancelOnboardingTaskProcessor implements ProcessorInterface
{
    public function __construct(private OnboardingTaskManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): OnboardingTask
    {
        /** @var CancelOnboardingTaskDto $data */
        return $this->manager->cancelFrom(new CancelOnboardingTaskModel($data->onboardingTaskId));
    }
}
