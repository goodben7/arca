<?php

namespace App\Tests\Unit\EventListener;

use App\Entity\MobilityRequest;
use App\Event\Domain\MobilityImplementedEvent;
use App\EventListener\RecordCompensationOnMobilityListener;
use App\Manager\CompensationHistoryManager;
use App\Model\MobilityRequestConstants;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RecordCompensationOnMobilityListenerTest extends TestCase
{
    private CompensationHistoryManager&MockObject $manager;
    private RecordCompensationOnMobilityListener $listener;

    protected function setUp(): void
    {
        $this->manager = $this->createMock(CompensationHistoryManager::class);
        $this->listener = new RecordCompensationOnMobilityListener($this->manager);
    }

    public function testDelegatesToCompensationHistoryManager(): void
    {
        $request = (new MobilityRequest())
            ->setEmployee('EMTEST001')
            ->setType(MobilityRequestConstants::TYPE_PROMOTION);

        $this->manager
            ->expects($this->once())
            ->method('recordFromMobility')
            ->with($request);

        $this->listener->onMobilityImplemented(new MobilityImplementedEvent($request, 'USTEST001'));
    }
}
