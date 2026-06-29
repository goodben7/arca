<?php

namespace App\EventListener;

use App\Event\Domain\OnboardingCompletedEvent;
use App\Exception\InvalidActionInputException;
use App\Manager\EmployeeManager;
use App\Model\ActivateEmployeeModel;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: OnboardingCompletedEvent::class, method: 'onOnboardingCompleted')]
class ActivateEmployeeOnOnboardingCompletedListener
{
    public function __construct(
        private EmployeeManager $employees,
    ) {
    }

    public function onOnboardingCompleted(OnboardingCompletedEvent $event): void
    {
        $employeeId = $event->getProcess()->getEmployee();
        if (null === $employeeId) {
            return;
        }

        try {
            $this->employees->activateFrom(new ActivateEmployeeModel($employeeId));
        } catch (InvalidActionInputException) {
            // Déjà actif ou transition non autorisée — activation automatique ignorée.
        }
    }
}
