<?php

namespace App\Tests\Unit\Manager;

use App\Entity\EvaluationCycle;
use App\Event\ActivityEvent;
use App\Exception\InvalidActionInputException;
use App\Manager\EvaluationCycleManager;
use App\Model\CloseEvaluationCycleModel;
use App\Model\EvaluationCycleConstants;
use App\Model\NewEvaluationCycleModel;
use App\Model\OpenEvaluationCycleModel;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

class EvaluationCycleManagerTest extends ManagerTestCase
{
    private EntityManagerInterface&MockObject $em;
    private ActivityEventDispatcher&MockObject $eventDispatcher;
    private EvaluationCycleManager $manager;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->eventDispatcher = $this->createMock(ActivityEventDispatcher::class);
        $this->manager = new EvaluationCycleManager($this->em, $this->eventDispatcher);
    }

    public function testCreateFromPersistsDraftCycle(): void
    {
        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');
        $this->eventDispatcher->expects($this->once())->method('dispatch');

        $cycle = $this->manager->createFrom(new NewEvaluationCycleModel(
            'Annual Review 2026',
            2026,
            new \DateTimeImmutable('2026-01-01'),
            new \DateTimeImmutable('2026-12-31'),
        ));

        self::assertSame(EvaluationCycleConstants::STATUS_DRAFT, $cycle->getStatus());
        self::assertSame('Annual Review 2026', $cycle->getName());
    }

    public function testOpenFromTransitionsDraftToOpen(): void
    {
        $cycle = (new EvaluationCycle())
            ->setName('Cycle')
            ->setYear(2026)
            ->setStartDate(new \DateTimeImmutable('2026-01-01'))
            ->setEndDate(new \DateTimeImmutable('2026-12-31'))
            ->setStatus(EvaluationCycleConstants::STATUS_DRAFT);
        $this->setEntityId($cycle, 'ECTEST001');

        $this->em->method('find')->willReturn($cycle);
        $this->em->expects($this->once())->method('flush');
        $this->eventDispatcher->expects($this->once())->method('dispatch');

        $result = $this->manager->openFrom(new OpenEvaluationCycleModel('ECTEST001'));

        self::assertSame(EvaluationCycleConstants::STATUS_OPEN, $result->getStatus());
        self::assertNotNull($result->getOpenedAt());
    }

    public function testCloseFromRejectsWhenNotOpen(): void
    {
        $cycle = (new EvaluationCycle())
            ->setName('Cycle')
            ->setYear(2026)
            ->setStartDate(new \DateTimeImmutable('2026-01-01'))
            ->setEndDate(new \DateTimeImmutable('2026-12-31'))
            ->setStatus(EvaluationCycleConstants::STATUS_DRAFT);
        $this->setEntityId($cycle, 'ECTEST001');

        $this->em->method('find')->willReturn($cycle);

        $this->expectException(InvalidActionInputException::class);

        $this->manager->closeFrom(new CloseEvaluationCycleModel('ECTEST001'));
    }
}
