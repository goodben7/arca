<?php

namespace App\Tests\Unit\Service;

use App\Entity\Employee;
use App\Entity\EmployeeJourneyEntry;
use App\Message\Query\QueryBusInterface;
use App\Model\EmployeeConstants;
use App\Model\JourneyEventTypeConstants;
use App\Model\JourneyStageConstants;
use App\Service\EmployeeJourneyRecorder;
use App\Tests\Unit\Manager\ManagerTestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\Security;

class EmployeeJourneyRecorderTest extends ManagerTestCase
{
    private EntityManagerInterface&MockObject $em;
    private Security&MockObject $security;
    private QueryBusInterface&MockObject $queries;
    private EmployeeJourneyRecorder $recorder;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->security = $this->createMock(Security::class);
        $this->queries = $this->createMock(QueryBusInterface::class);

        $this->security->method('getUser')->willReturn(null);

        $this->recorder = new EmployeeJourneyRecorder(
            $this->em,
            $this->security,
            $this->queries,
        );
    }

    public function testRecordPersistsJourneyEntry(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);

        $persistedEntry = null;
        $this->em->expects($this->once())->method('persist')->willReturnCallback(function (EmployeeJourneyEntry $entry) use (&$persistedEntry): void {
            $persistedEntry = $entry;
        });
        $this->em->expects($this->once())->method('flush')->willReturnCallback(function () use (&$persistedEntry): void {
            if ($persistedEntry instanceof EmployeeJourneyEntry) {
                $this->setEntityId($persistedEntry, 'EJTEST001');
            }
        });

        $entry = $this->recorder->record(
            employee: $employee,
            stage: JourneyStageConstants::ACTIVE,
            eventType: JourneyEventTypeConstants::ACTIVATED,
            sourceEntityType: 'EMPLOYEE',
            sourceEntityId: 'EMTEST001',
            metadata: ['previousStatus' => EmployeeConstants::STATUS_INACTIVE],
            description: 'employee activated',
        );

        self::assertSame('EJTEST001', $entry->getId());
        self::assertSame('EMTEST001', $entry->getEmployeeId());
        self::assertSame(JourneyStageConstants::ACTIVE, $entry->getStage());
        self::assertSame(JourneyEventTypeConstants::ACTIVATED, $entry->getEventType());
        self::assertSame('SYSTEM', $entry->getActorId());
        self::assertSame('employee activated', $entry->getDescription());
        self::assertSame(['previousStatus' => EmployeeConstants::STATUS_INACTIVE], $entry->getMetadata());
    }

    public function testRecordThrowsForInvalidStage(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);

        $this->em->expects($this->never())->method('persist');

        $this->expectException(\InvalidArgumentException::class);

        $this->recorder->record(
            employee: $employee,
            stage: 'INVALID_STAGE',
            eventType: JourneyEventTypeConstants::ACTIVATED,
        );
    }
}
