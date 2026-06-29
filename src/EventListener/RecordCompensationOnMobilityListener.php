<?php

namespace App\EventListener;

use App\Event\Domain\MobilityImplementedEvent;
use App\Manager\CompensationHistoryManager;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: MobilityImplementedEvent::class, method: 'onMobilityImplemented', priority: 0)]
class RecordCompensationOnMobilityListener
{
    public function __construct(
        private CompensationHistoryManager $compensationHistory,
    ) {
    }

    public function onMobilityImplemented(MobilityImplementedEvent $event): void
    {
        $this->compensationHistory->recordFromMobility($event->getMobilityRequest());
    }
}
