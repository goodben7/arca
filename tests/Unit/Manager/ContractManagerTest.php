<?php

namespace App\Tests\Unit\Manager;

use App\Entity\Contract;
use App\Event\Domain\ContractActivatedEvent;
use App\Exception\InvalidActionInputException;
use App\Manager\ContractManager;
use App\Message\Query\QueryBusInterface;
use App\Model\ActivateContractModel;
use App\Model\CancelContractModel;
use App\Model\ContractConstants;
use App\Model\EndContractModel;
use App\Model\SetContractPendingModel;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ContractManagerTest extends ManagerTestCase
{
    private EntityManagerInterface&MockObject $em;
    private Security&MockObject $security;
    private QueryBusInterface&MockObject $queries;
    private EventDispatcherInterface&MockObject $domainEventDispatcher;
    private ContractManager $manager;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->security = $this->createMock(Security::class);
        $this->queries = $this->createMock(QueryBusInterface::class);
        $this->domainEventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->security->method('getUser')->willReturn(null);

        $this->manager = new ContractManager(
            $this->em,
            $this->security,
            $this->queries,
            $this->domainEventDispatcher,
        );
    }

    public function testActivateFromTransitionsPendingToActive(): void
    {
        $contract = $this->createContract('CTTEST001', ContractConstants::STATUS_PENDING);

        $this->em->method('find')->willReturn($contract);
        $this->em->expects($this->once())->method('flush');
        $this->domainEventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ContractActivatedEvent::class));

        $result = $this->manager->activateFrom(new ActivateContractModel('CTTEST001'));

        self::assertSame(ContractConstants::STATUS_ACTIVE, $result->getStatus());
        self::assertNotNull($result->getActivatedAt());
        self::assertSame('SYSTEM', $result->getActivatedBy());
    }

    public function testEndFromTransitionsActiveToEnded(): void
    {
        $contract = $this->createContract('CTTEST001', ContractConstants::STATUS_ACTIVE);

        $this->em->method('find')->willReturn($contract);
        $this->em->expects($this->once())->method('flush');

        $result = $this->manager->endFrom(new EndContractModel('CTTEST001'));

        self::assertSame(ContractConstants::STATUS_ENDED, $result->getStatus());
        self::assertNotNull($result->getEndedAt());
        self::assertNotNull($result->getEndDate());
    }

    public function testCancelFromTransitionsPendingToCancelled(): void
    {
        $contract = $this->createContract('CTTEST001', ContractConstants::STATUS_PENDING);

        $this->em->method('find')->willReturn($contract);
        $this->em->expects($this->once())->method('flush');

        $result = $this->manager->cancelFrom(new CancelContractModel('CTTEST001'));

        self::assertSame(ContractConstants::STATUS_CANCELLED, $result->getStatus());
        self::assertNotNull($result->getCancelledAt());
    }

    public function testSetPendingFromTransitionsActiveToPending(): void
    {
        $contract = $this->createContract('CTTEST001', ContractConstants::STATUS_ACTIVE);

        $this->em->method('find')->willReturn($contract);
        $this->em->expects($this->once())->method('flush');

        $result = $this->manager->setPendingFrom(new SetContractPendingModel('CTTEST001'));

        self::assertSame(ContractConstants::STATUS_PENDING, $result->getStatus());
        self::assertNotNull($result->getPendingAt());
    }

    public function testActivateFromThrowsWhenContractIsEnded(): void
    {
        $contract = $this->createContract('CTTEST001', ContractConstants::STATUS_ENDED);

        $this->em->method('find')->willReturn($contract);
        $this->em->expects($this->never())->method('flush');

        $this->expectException(InvalidActionInputException::class);
        $this->expectExceptionMessage('Action not allowed : invalid contract state');

        $this->manager->activateFrom(new ActivateContractModel('CTTEST001'));
    }

    public function testEndFromPreservesExistingEndDate(): void
    {
        $contract = $this->createContract('CTTEST001', ContractConstants::STATUS_ACTIVE);
        $existingEndDate = new \DateTimeImmutable('2025-12-31');
        $contract->setEndDate($existingEndDate);

        $this->em->method('find')->willReturn($contract);
        $this->em->expects($this->once())->method('flush');

        $result = $this->manager->endFrom(new EndContractModel('CTTEST001'));

        self::assertSame($existingEndDate, $result->getEndDate());
    }
}
