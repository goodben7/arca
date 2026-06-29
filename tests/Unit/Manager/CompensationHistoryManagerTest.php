<?php

namespace App\Tests\Unit\Manager;

use App\Compensation\CompensationPolicyInterface;
use App\Compensation\CompensationProposal;
use App\Entity\CompensationHistory;
use App\Entity\Contract;
use App\Entity\Employee;
use App\Entity\MobilityRequest;
use App\Manager\CompensationHistoryManager;
use App\Message\NotifyPayrollMessage;
use App\Model\CompensationHistoryConstants;
use App\Model\ContractConstants;
use App\Model\EmployeeConstants;
use App\Model\MobilityRequestConstants;
use App\Model\RecordCompensationHistoryModel;
use App\Repository\ContractRepository;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class CompensationHistoryManagerTest extends ManagerTestCase
{
    private EntityManagerInterface&MockObject $em;
    private ActivityEventDispatcher&MockObject $eventDispatcher;
    private ContractRepository&MockObject $contracts;
    private CompensationPolicyInterface&MockObject $policy;
    private MessageBusInterface&MockObject $messageBus;
    private CompensationHistoryManager $manager;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->eventDispatcher = $this->createMock(ActivityEventDispatcher::class);
        $this->contracts = $this->createMock(ContractRepository::class);
        $this->policy = $this->createMock(CompensationPolicyInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);

        $this->manager = new CompensationHistoryManager(
            $this->em,
            $this->eventDispatcher,
            $this->contracts,
            $this->policy,
            $this->messageBus,
        );
    }

    public function testRecordFromCreatesHistoryAndNotifiesPayroll(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);
        $contract = $this->createContract('CTTEST001', ContractConstants::STATUS_ACTIVE);

        $this->em->method('find')->willReturnCallback(function (string $class, $id) use ($employee) {
            if (Employee::class === $class) {
                return $employee;
            }

            return null;
        });
        $this->contracts->method('findActiveByEmployee')->with('EMTEST001')->willReturn($contract);
        $this->em->expects($this->once())->method('persist')->with(self::isInstanceOf(CompensationHistory::class));
        $this->em->expects($this->once())->method('flush');
        $this->messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(NotifyPayrollMessage::class))
            ->willReturn(new Envelope(new \stdClass()));

        $history = $this->manager->recordFrom(new RecordCompensationHistoryModel(
            'EMTEST001',
            '52000.00',
            new \DateTimeImmutable('2026-07-01'),
            'annual review',
        ));

        self::assertSame('EMTEST001', $history->getEmployee());
        self::assertSame('45000.00', $history->getOldSalary());
        self::assertSame('52000.00', $history->getNewSalary());
        self::assertSame(CompensationHistoryConstants::SOURCE_MANUAL, $history->getSourceEvent());
        self::assertSame('52000.00', $contract->getSalary());
    }

    public function testRecordFromMobilitySkipsTransfer(): void
    {
        $request = (new MobilityRequest())
            ->setEmployee('EMTEST001')
            ->setType(MobilityRequestConstants::TYPE_TRANSFER);

        self::assertNull($this->manager->recordFromMobility($request));
    }

    public function testRecordFromMobilityAppliesPolicyForPromotion(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);
        $employee->setGrade($this->createGrade('GRTEST004', 'G4', 4));
        $contract = $this->createContract('CTTEST001', ContractConstants::STATUS_ACTIVE);

        $request = (new MobilityRequest())
            ->setEmployee('EMTEST001')
            ->setType(MobilityRequestConstants::TYPE_PROMOTION)
            ->setImplementedAt(new \DateTimeImmutable('2026-07-02'));
        $this->setEntityId($request, 'MBTEST001');

        $this->em->method('find')->with(Employee::class, 'EMTEST001')->willReturn($employee);
        $this->contracts->method('findActiveByEmployee')->with('EMTEST001')->willReturn($contract);
        $this->policy
            ->method('computeNewSalary')
            ->willReturn(new CompensationProposal('48000.00', 'grade-based salary for G4 (rank 4)'));
        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');
        $this->messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(NotifyPayrollMessage::class))
            ->willReturn(new Envelope(new \stdClass()));

        $history = $this->manager->recordFromMobility($request);

        self::assertNotNull($history);
        self::assertSame(CompensationHistoryConstants::SOURCE_MOBILITY_IMPLEMENTED, $history->getSourceEvent());
        self::assertSame('48000.00', $contract->getSalary());
    }
}
