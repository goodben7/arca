<?php

namespace App\EventListener;

use App\Event\Domain\EmployeeCreatedEvent;
use App\Manager\OnboardingProcessManager;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: EmployeeCreatedEvent::class, method: 'onEmployeeCreated')]
class StartOnboardingOnEmployeeCreatedListener
{
    public function __construct(
        private OnboardingProcessManager $onboardingProcesses,
    ) {
    }

    public function onEmployeeCreated(EmployeeCreatedEvent $event): void
    {
        $this->onboardingProcesses->startForEmployee(
            $event->getEmployee(),
            $event->getActorId(),
        );
    }
}
