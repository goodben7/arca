<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\StartOnboardingTaskDto;
use App\Entity\OnboardingTask;
use App\Manager\OnboardingTaskManager;
use App\Model\StartOnboardingTaskModel;

class StartOnboardingTaskProcessor implements ProcessorInterface
{
    public function __construct(private OnboardingTaskManager $manager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): OnboardingTask
    {
        /** @var StartOnboardingTaskDto $data */
        return $this->manager->startFrom(new StartOnboardingTaskModel($data->onboardingTaskId));
    }
}
