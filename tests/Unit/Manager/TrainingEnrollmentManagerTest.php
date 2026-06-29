<?php

namespace App\Tests\Unit\Manager;

use App\Entity\TrainingEnrollment;
use App\Event\Domain\TrainingEnrollmentCertifiedEvent;
use App\Event\Domain\TrainingEnrollmentCompletedEvent;
use App\Event\Domain\TrainingEnrollmentStartedEvent;
use App\Exception\InvalidActionInputException;
use App\Manager\TrainingEnrollmentManager;
use App\Message\Query\QueryBusInterface;
use App\Model\CertifyTrainingEnrollmentModel;
use App\Model\NewTrainingEnrollmentModel;
use App\Model\TrainingEnrollmentConstants;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class TrainingEnrollmentManagerTest extends ManagerTestCase
{
    private EntityManagerInterface&MockObject $em;
    private ActivityEventDispatcher&MockObject $eventDispatcher;
    private Security&MockObject $security;
    private QueryBusInterface&MockObject $queries;
    private EventDispatcherInterface&MockObject $domainEventDispatcher;
    private TrainingEnrollmentManager $manager;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->eventDispatcher = $this->createMock(ActivityEventDispatcher::class);
        $this->security = $this->createMock(Security::class);
        $this->queries = $this->createMock(QueryBusInterface::class);
        $this->domainEventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->security->method('getUser')->willReturn(null);

        $this->manager = new TrainingEnrollmentManager(
            $this->em,
            $this->eventDispatcher,
            $this->security,
            $this->queries,
            $this->domainEventDispatcher,
        );
    }

    public function testWorkflowAssignedToCertified(): void
    {
        $enrollment = (new TrainingEnrollment())
            ->setEmployee('EMTEST001')
            ->setTrainingSession('TSTEST001')
            ->setStatus(TrainingEnrollmentConstants::STATUS_ASSIGNED);
        $this->setEntityId($enrollment, 'TETEST001');

        $this->em->method('find')->willReturnCallback(function (string $class, $id) use ($enrollment) {
            if (TrainingEnrollment::class === $class) {
                return $enrollment;
            }

            return $this->createEmployee('EMTEST001', 'ACTIVE');
        });

        $this->em->expects($this->exactly(3))->method('flush');
        $this->domainEventDispatcher
            ->expects($this->exactly(3))
            ->method('dispatch')
            ->willReturnCallback(function (object $event): object {
                static $count = 0;
                ++$count;
                if (1 === $count) {
                    self::assertInstanceOf(TrainingEnrollmentStartedEvent::class, $event);
                }
                if (2 === $count) {
                    self::assertInstanceOf(TrainingEnrollmentCompletedEvent::class, $event);
                }
                if (3 === $count) {
                    self::assertInstanceOf(TrainingEnrollmentCertifiedEvent::class, $event);
                }

                return $event;
            });

        $this->manager->start('TETEST001');
        self::assertSame(TrainingEnrollmentConstants::STATUS_IN_PROGRESS, $enrollment->getStatus());

        $this->manager->complete('TETEST001');
        self::assertSame(TrainingEnrollmentConstants::STATUS_COMPLETED, $enrollment->getStatus());

        $this->manager->certifyFrom(new CertifyTrainingEnrollmentModel('TETEST001', 85.5, 'CERT-2026-001'));
        self::assertSame(TrainingEnrollmentConstants::STATUS_CERTIFIED, $enrollment->getStatus());
        self::assertSame('85.5', $enrollment->getScore());
        self::assertSame('CERT-2026-001', $enrollment->getCertificate());
    }

    public function testCertifyRequiresCertificate(): void
    {
        $enrollment = (new TrainingEnrollment())
            ->setEmployee('EMTEST001')
            ->setTrainingSession('TSTEST001')
            ->setStatus(TrainingEnrollmentConstants::STATUS_COMPLETED);
        $this->setEntityId($enrollment, 'TETEST001');

        $this->em->method('find')->willReturn($enrollment);

        $this->expectException(InvalidActionInputException::class);

        $this->manager->certifyFrom(new CertifyTrainingEnrollmentModel('TETEST001'));
    }
}
