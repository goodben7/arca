<?php

namespace App\Tests\Unit\Manager;

use App\Entity\OnboardingProcess;
use App\Entity\OnboardingTask;
use App\Event\Domain\OnboardingCompletedEvent;
use App\Event\Domain\OnboardingStartedEvent;
use App\Exception\InvalidActionInputException;
use App\Manager\OnboardingProcessManager;
use App\Message\Query\QueryBusInterface;
use App\Model\CompleteOnboardingProcessModel;
use App\Model\OnboardingProcessConstants;
use App\Model\OnboardingTaskConstants;
use App\Repository\OnboardingProcessRepository;
use App\Repository\OnboardingTaskRepository;
use App\Service\ActivityEventDispatcher;
use App\Service\OnboardingChecklistProvider;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class OnboardingProcessManagerTest extends ManagerTestCase
{
    private EntityManagerInterface&MockObject $em;
    private ActivityEventDispatcher&MockObject $eventDispatcher;
    private Security&MockObject $security;
    private QueryBusInterface&MockObject $queries;
    private EventDispatcherInterface&MockObject $domainEventDispatcher;
    private OnboardingProcessRepository&MockObject $processRepository;
    private OnboardingTaskRepository&MockObject $taskRepository;
    private OnboardingChecklistProvider&MockObject $checklistProvider;
    private OnboardingProcessManager $manager;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->eventDispatcher = $this->createMock(ActivityEventDispatcher::class);
        $this->security = $this->createMock(Security::class);
        $this->queries = $this->createMock(QueryBusInterface::class);
        $this->domainEventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->processRepository = $this->createMock(OnboardingProcessRepository::class);
        $this->taskRepository = $this->createMock(OnboardingTaskRepository::class);
        $this->checklistProvider = $this->createMock(OnboardingChecklistProvider::class);

        $this->security->method('getUser')->willReturn(null);

        $this->manager = new OnboardingProcessManager(
            $this->em,
            $this->eventDispatcher,
            $this->security,
            $this->queries,
            $this->domainEventDispatcher,
            $this->processRepository,
            $this->taskRepository,
            $this->checklistProvider,
        );
    }

    public function testStartForEmployeeCreatesProcessWithChecklistTasks(): void
    {
        $employee = $this->createEmployee('EMTEST001', 'INACTIVE');

        $this->processRepository
            ->method('findActiveForEmployee')
            ->with('EMTEST001')
            ->willReturn(null);

        $this->checklistProvider
            ->method('getDefaultItems')
            ->willReturn([
                ['title' => 'Dossier admin', 'type' => OnboardingTaskConstants::TYPE_HR_FORM, 'dueDays' => 7],
                ['title' => 'Accès IT', 'type' => OnboardingTaskConstants::TYPE_IT_ACCESS, 'dueDays' => 3],
            ]);

        $persisted = [];
        $this->em->expects($this->exactly(3))->method('persist')->willReturnCallback(function (object $entity) use (&$persisted): void {
            $persisted[] = $entity;
        });
        $this->em->expects($this->once())->method('flush')->willReturnCallback(function () use (&$persisted): void {
            foreach ($persisted as $entity) {
                if ($entity instanceof OnboardingProcess) {
                    $this->setEntityId($entity, 'OPTEST001');
                }
            }
        });

        $this->eventDispatcher->expects($this->once())->method('dispatch');
        $this->domainEventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(OnboardingStartedEvent::class));

        $process = $this->manager->startForEmployee($employee);

        self::assertSame('EMTEST001', $process->getEmployee());
        self::assertSame(OnboardingProcessConstants::STATUS_IN_PROGRESS, $process->getStatus());
        self::assertNotNull($process->getStartedAt());

        $tasks = array_values(array_filter($persisted, fn (object $e): bool => $e instanceof OnboardingTask));
        self::assertCount(2, $tasks);
        self::assertSame(OnboardingTaskConstants::STATUS_PENDING, $tasks[0]->getStatus());
    }

    public function testStartForEmployeeRejectsWhenActiveProcessExists(): void
    {
        $employee = $this->createEmployee('EMTEST001', 'INACTIVE');
        $existing = (new OnboardingProcess())
            ->setEmployee('EMTEST001')
            ->setStatus(OnboardingProcessConstants::STATUS_IN_PROGRESS);
        $this->setEntityId($existing, 'OPTEST000');

        $this->processRepository
            ->method('findActiveForEmployee')
            ->with('EMTEST001')
            ->willReturn($existing);

        $this->expectException(InvalidActionInputException::class);

        $this->manager->startForEmployee($employee);
    }

    public function testCompleteFromTransitionsInProgressToCompleted(): void
    {
        $process = (new OnboardingProcess())
            ->setEmployee('EMTEST001')
            ->setStatus(OnboardingProcessConstants::STATUS_IN_PROGRESS)
            ->setStartedAt(new \DateTimeImmutable());
        $this->setEntityId($process, 'OPTEST001');

        $this->em->method('find')->willReturn($process);
        $this->taskRepository->method('countOpenByProcess')->willReturn(0);
        $this->taskRepository->method('countCompletedByProcess')->willReturn(2);
        $this->em->expects($this->once())->method('flush');
        $this->eventDispatcher->expects($this->once())->method('dispatch');
        $this->domainEventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(OnboardingCompletedEvent::class));

        $result = $this->manager->completeFrom(new CompleteOnboardingProcessModel('OPTEST001'));

        self::assertSame(OnboardingProcessConstants::STATUS_COMPLETED, $result->getStatus());
        self::assertNotNull($result->getCompletedAt());
    }

    public function testCompleteFromRejectsWhenOpenTasksRemain(): void
    {
        $process = (new OnboardingProcess())
            ->setEmployee('EMTEST001')
            ->setStatus(OnboardingProcessConstants::STATUS_IN_PROGRESS);
        $this->setEntityId($process, 'OPTEST001');

        $this->em->method('find')->willReturn($process);
        $this->taskRepository->method('countOpenByProcess')->willReturn(1);

        $this->expectException(InvalidActionInputException::class);

        $this->manager->completeFrom(new CompleteOnboardingProcessModel('OPTEST001'));
    }
}
