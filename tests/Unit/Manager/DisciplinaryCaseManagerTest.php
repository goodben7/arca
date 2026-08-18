<?php

namespace App\Tests\Unit\Manager;

use App\Entity\DisciplinaryCase;
use App\Entity\Document;
use App\Entity\Employee;
use App\Entity\ExitProcess;
use App\Entity\SanctionScale;
use App\Event\Domain\DisciplinaryCaseOpenedEvent;
use App\Event\Domain\DisciplinarySanctionAppliedEvent;
use App\Exception\InvalidActionInputException;
use App\Manager\DisciplinaryCaseManager;
use App\Manager\EmployeeManager;
use App\Manager\ExitProcessManager;
use App\Message\Query\QueryBusInterface;
use App\Model\ApplyDisciplinarySanctionModel;
use App\Model\DecideDisciplinaryCaseModel;
use App\Model\DisciplinaryCaseConstants;
use App\Model\EmployeeConstants;
use App\Model\ExitProcessConstants;
use App\Model\NewDisciplinaryCaseModel;
use App\Model\NewExitProcessModel;
use App\Model\OpenDisciplinaryCaseModel;
use App\Model\RequestDisciplinaryExplanationModel;
use App\Model\SanctionScaleConstants;
use App\Model\StartExitProcessModel;
use App\Model\SuspendEmployeeModel;
use App\Policy\DisciplinaryRecidivismPolicy;
use App\Repository\DisciplinaryCaseRepository;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DisciplinaryCaseManagerTest extends ManagerTestCase
{
    private EntityManagerInterface&MockObject $em;
    private EventDispatcherInterface&MockObject $domainEvents;
    private EmployeeManager&MockObject $employees;
    private ExitProcessManager&MockObject $exitProcesses;
    private DisciplinaryCaseRepository&MockObject $disciplinaryCases;
    private DisciplinaryRecidivismPolicy&MockObject $recidivism;
    private DisciplinaryCaseManager $manager;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->domainEvents = $this->createMock(EventDispatcherInterface::class);
        $this->employees = $this->createMock(EmployeeManager::class);
        $this->exitProcesses = $this->createMock(ExitProcessManager::class);
        $this->disciplinaryCases = $this->createMock(DisciplinaryCaseRepository::class);
        $this->recidivism = $this->createMock(DisciplinaryRecidivismPolicy::class);

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn(null);

        $this->manager = new DisciplinaryCaseManager(
            $this->em,
            $this->createMock(ActivityEventDispatcher::class),
            $security,
            $this->createMock(QueryBusInterface::class),
            $this->domainEvents,
            $this->employees,
            $this->exitProcesses,
            $this->disciplinaryCases,
            $this->recidivism,
        );
    }

    public function testCreateDraftCaseForActiveEmployee(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);
        $scale = $this->createWarnScale();

        $this->em->method('find')->willReturnCallback(function (string $class, $id) use ($employee, $scale) {
            return match (true) {
                Employee::class === $class && 'EMTEST001' === $id => $employee,
                SanctionScale::class === $class && 'SSTEST001' === $id => $scale,
                default => null,
            };
        });
        $this->disciplinaryCases->method('findActiveForEmployee')->with('EMTEST001')->willReturn(null);
        $this->em->expects($this->once())->method('persist')->with(self::isInstanceOf(DisciplinaryCase::class));
        $this->em->expects($this->once())->method('flush');

        $result = $this->manager->createFrom(new NewDisciplinaryCaseModel(
            'EMTEST001',
            'SSTEST001',
            'Faits disciplinaires',
            new \DateTimeImmutable('2026-07-01'),
        ));

        self::assertSame(DisciplinaryCaseConstants::STATUS_DRAFT, $result->getStatus());
        self::assertSame('EMTEST001', $result->getEmployee());
        self::assertSame($scale, $result->getSanctionScale());
    }

    public function testCreateRejectsWhenActiveCaseExists(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);
        $scale = $this->createWarnScale();
        $activeCase = (new DisciplinaryCase())
            ->setEmployee('EMTEST001')
            ->setSanctionScale($scale)
            ->setFacts('Faits')
            ->setOccurredAt(new \DateTimeImmutable('2026-07-01'))
            ->setStatus(DisciplinaryCaseConstants::STATUS_OPENED);

        $this->em->method('find')->willReturnCallback(function (string $class, $id) use ($employee, $scale) {
            return match (true) {
                Employee::class === $class && 'EMTEST001' === $id => $employee,
                SanctionScale::class === $class && 'SSTEST001' === $id => $scale,
                default => null,
            };
        });
        $this->disciplinaryCases
            ->method('findActiveForEmployee')
            ->with('EMTEST001')
            ->willReturn($activeCase);

        $this->expectException(InvalidActionInputException::class);
        $this->expectExceptionMessage('an active disciplinary case already exists for this employee');

        $this->manager->createFrom(new NewDisciplinaryCaseModel(
            'EMTEST001',
            'SSTEST001',
            'Faits disciplinaires',
            new \DateTimeImmutable('2026-07-01'),
        ));
    }

    public function testCreateRejectsWhenRecidivismPolicyBlocks(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);
        $scale = $this->createWarnScale();

        $this->em->method('find')->willReturnCallback(function (string $class, $id) use ($employee, $scale) {
            return match (true) {
                Employee::class === $class && 'EMTEST001' === $id => $employee,
                SanctionScale::class === $class && 'SSTEST001' === $id => $scale,
                default => null,
            };
        });
        $this->disciplinaryCases->method('findActiveForEmployee')->with('EMTEST001')->willReturn(null);
        $this->recidivism
            ->expects($this->once())
            ->method('assertAllowed')
            ->with('EMTEST001', $scale, false)
            ->willThrowException(new InvalidActionInputException('recidivism: same severity 2'));

        $this->expectException(InvalidActionInputException::class);
        $this->expectExceptionMessage('recidivism: same severity 2');

        $this->manager->createFrom(new NewDisciplinaryCaseModel(
            'EMTEST001',
            'SSTEST001',
            'Faits disciplinaires',
            new \DateTimeImmutable('2026-07-01'),
        ));
    }

    public function testOpenDispatchesDisciplinaryCaseOpenedEvent(): void
    {
        $scale = $this->createWarnScale();
        $case = (new DisciplinaryCase())
            ->setEmployee('EMTEST001')
            ->setSanctionScale($scale)
            ->setFacts('Faits')
            ->setOccurredAt(new \DateTimeImmutable('2026-07-01'))
            ->setStatus(DisciplinaryCaseConstants::STATUS_DRAFT);
        $this->setEntityId($case, 'DSTEST001');

        $this->em->method('find')->willReturn($case);
        $this->em->expects($this->once())->method('flush');
        $this->domainEvents
            ->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(DisciplinaryCaseOpenedEvent::class));

        $result = $this->manager->openFrom(new OpenDisciplinaryCaseModel('DSTEST001'));

        self::assertSame(DisciplinaryCaseConstants::STATUS_OPENED, $result->getStatus());
        self::assertNotNull($result->getOpenedAt());
    }

    public function testRequestExplanationFromOpened(): void
    {
        $scale = $this->createWarnScale();
        $case = (new DisciplinaryCase())
            ->setEmployee('EMTEST001')
            ->setSanctionScale($scale)
            ->setFacts('Faits')
            ->setOccurredAt(new \DateTimeImmutable('2026-07-01'))
            ->setStatus(DisciplinaryCaseConstants::STATUS_OPENED);
        $this->setEntityId($case, 'DSTEST001');

        $this->em->method('find')->willReturn($case);
        $this->em->expects($this->once())->method('flush');

        $result = $this->manager->requestExplanationFrom(new RequestDisciplinaryExplanationModel(
            'DSTEST001',
            null,
            'Je conteste les faits.',
        ));

        self::assertSame(DisciplinaryCaseConstants::STATUS_EXPLANATION_REQUESTED, $result->getStatus());
        self::assertNotNull($result->getExplanationRequestedAt());
        self::assertNotNull($result->getExplanationDueAt());
        self::assertSame('Je conteste les faits.', $result->getExplanationText());
    }

    public function testDecideFromOpenedRejectedUntilExplanationRequested(): void
    {
        $scale = $this->createWarnScale();
        $case = (new DisciplinaryCase())
            ->setEmployee('EMTEST001')
            ->setSanctionScale($scale)
            ->setFacts('Faits')
            ->setOccurredAt(new \DateTimeImmutable('2026-07-01'))
            ->setStatus(DisciplinaryCaseConstants::STATUS_OPENED);
        $this->setEntityId($case, 'DSTEST001');

        $this->em->method('find')->willReturn($case);
        $this->expectException(InvalidActionInputException::class);

        $this->manager->decideFrom(new DecideDisciplinaryCaseModel('DSTEST001'));
    }

    public function testDecideFromExplanationRequestedWhenHearingNotRequired(): void
    {
        $scale = $this->createWarnScale();
        $case = (new DisciplinaryCase())
            ->setEmployee('EMTEST001')
            ->setSanctionScale($scale)
            ->setFacts('Faits')
            ->setOccurredAt(new \DateTimeImmutable('2026-07-01'))
            ->setStatus(DisciplinaryCaseConstants::STATUS_EXPLANATION_REQUESTED);
        $this->setEntityId($case, 'DSTEST001');

        $this->em->method('find')->willReturn($case);
        $this->em->expects($this->once())->method('flush');

        $result = $this->manager->decideFrom(new DecideDisciplinaryCaseModel('DSTEST001', 'Décision'));

        self::assertSame(DisciplinaryCaseConstants::STATUS_DECISION_PENDING, $result->getStatus());
        self::assertSame('Décision', $result->getReason());
        self::assertNotNull($result->getDecidedAt());
    }

    public function testApplyWithSuspendCallsEmployeeManager(): void
    {
        $scale = (new SanctionScale())
            ->setCode(SanctionScaleConstants::CODE_SUSPEND)
            ->setLabel('Mise à pied disciplinaire')
            ->setSeverityLevel(4)
            ->setRequiresHearing(true)
            ->setActive(true);
        $this->setEntityId($scale, 'SSTEST003');

        $case = (new DisciplinaryCase())
            ->setEmployee('EMTEST001')
            ->setSanctionScale($scale)
            ->setFacts('Faits')
            ->setOccurredAt(new \DateTimeImmutable('2026-07-01'))
            ->setStatus(DisciplinaryCaseConstants::STATUS_DECISION_PENDING);
        $this->setEntityId($case, 'DSTEST001');

        $this->em->method('find')->willReturn($case);
        $this->employees
            ->expects($this->once())
            ->method('suspendFrom')
            ->with(self::callback(static function (SuspendEmployeeModel $model): bool {
                return 'EMTEST001' === $model->employeeId;
            }));
        $this->domainEvents
            ->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(DisciplinarySanctionAppliedEvent::class));
        $this->em->expects($this->once())->method('flush');

        $result = $this->manager->applyFrom(new ApplyDisciplinarySanctionModel('DSTEST001'));

        self::assertSame(DisciplinaryCaseConstants::STATUS_SANCTION_APPLIED, $result->getStatus());
    }

    public function testApplyWarnCreatesDocument(): void
    {
        $scale = $this->createWarnScale();
        $case = (new DisciplinaryCase())
            ->setEmployee('EMTEST001')
            ->setSanctionScale($scale)
            ->setFacts('Faits')
            ->setOccurredAt(new \DateTimeImmutable('2026-07-01'))
            ->setStatus(DisciplinaryCaseConstants::STATUS_DECISION_PENDING);
        $this->setEntityId($case, 'DSTEST001');

        $this->em->method('find')->willReturn($case);
        $this->em
            ->expects($this->once())
            ->method('persist')
            ->with(self::callback(static function (Document $document): bool {
                return Document::TYPE_WARNING_LETTER === $document->getType()
                    && 'Sanction disciplinaire — Avertissement' === $document->getTitle()
                    && 'DSTEST001' === $document->getDocumentRefNumber();
            }));
        $this->domainEvents
            ->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(DisciplinarySanctionAppliedEvent::class));
        $this->em->expects($this->once())->method('flush');

        $result = $this->manager->applyFrom(new ApplyDisciplinarySanctionModel('DSTEST001'));

        self::assertSame(DisciplinaryCaseConstants::STATUS_SANCTION_APPLIED, $result->getStatus());
        self::assertInstanceOf(Document::class, $result->getDocument());
        self::assertSame(Document::TYPE_WARNING_LETTER, $result->getDocument()->getType());
        self::assertNotNull($result->getAppealDeadlineAt());
        self::assertSame(
            $result->getAppliedAt()?->modify('+'.DisciplinaryCaseConstants::APPEAL_DEADLINE_DAYS.' days')?->format('Y-m-d'),
            $result->getAppealDeadlineAt()->format('Y-m-d'),
        );
    }

    public function testApplyReprimandCreatesDocument(): void
    {
        $scale = (new SanctionScale())
            ->setCode(SanctionScaleConstants::CODE_REPRIMAND)
            ->setLabel('Réprimande / Observation')
            ->setSeverityLevel(1)
            ->setRequiresHearing(false)
            ->setActive(true);
        $this->setEntityId($scale, 'SSTEST000');

        $case = (new DisciplinaryCase())
            ->setEmployee('EMTEST001')
            ->setSanctionScale($scale)
            ->setFacts('Faits')
            ->setOccurredAt(new \DateTimeImmutable('2026-07-01'))
            ->setStatus(DisciplinaryCaseConstants::STATUS_DECISION_PENDING);
        $this->setEntityId($case, 'DSTEST001');

        $this->em->method('find')->willReturn($case);
        $this->em
            ->expects($this->once())
            ->method('persist')
            ->with(self::callback(static function (Document $document): bool {
                return Document::TYPE_WARNING_LETTER === $document->getType()
                    && 'Sanction disciplinaire — Réprimande / Observation' === $document->getTitle();
            }));
        $this->em->expects($this->once())->method('flush');

        $result = $this->manager->applyFrom(new ApplyDisciplinarySanctionModel('DSTEST001'));

        self::assertInstanceOf(Document::class, $result->getDocument());
        self::assertSame(Document::TYPE_WARNING_LETTER, $result->getDocument()->getType());
    }

    public function testApplyDismissCreatesExitProcess(): void
    {
        $scale = (new SanctionScale())
            ->setCode(SanctionScaleConstants::CODE_DISMISS)
            ->setLabel('Licenciement pour faute')
            ->setSeverityLevel(5)
            ->setRequiresHearing(true)
            ->setActive(true);
        $this->setEntityId($scale, 'SSTEST004');

        $case = (new DisciplinaryCase())
            ->setEmployee('EMTEST001')
            ->setSanctionScale($scale)
            ->setFacts('Faits')
            ->setOccurredAt(new \DateTimeImmutable('2026-07-01'))
            ->setStatus(DisciplinaryCaseConstants::STATUS_DECISION_PENDING);
        $this->setEntityId($case, 'DSTEST001');

        $exitProcess = (new ExitProcess())
            ->setEmployee('EMTEST001')
            ->setReason(ExitProcessConstants::REASON_DISMISSAL)
            ->setDepartureDate(new \DateTimeImmutable('today'))
            ->setStatus(ExitProcessConstants::STATUS_PENDING);
        $this->setEntityId($exitProcess, 'EPTEST001');

        $startedExit = (new ExitProcess())
            ->setEmployee('EMTEST001')
            ->setReason(ExitProcessConstants::REASON_DISMISSAL)
            ->setDepartureDate(new \DateTimeImmutable('today'))
            ->setStatus(ExitProcessConstants::STATUS_IN_PROGRESS)
            ->setStartedAt(new \DateTimeImmutable());
        $this->setEntityId($startedExit, 'EPTEST001');

        $this->em->method('find')->willReturn($case);
        $this->exitProcesses
            ->expects($this->once())
            ->method('createFrom')
            ->with(self::callback(static function (NewExitProcessModel $model): bool {
                return 'EMTEST001' === $model->employee
                    && ExitProcessConstants::REASON_DISMISSAL === $model->reason;
            }))
            ->willReturn($exitProcess);
        $this->exitProcesses
            ->expects($this->once())
            ->method('startFrom')
            ->with(self::callback(static function (StartExitProcessModel $model): bool {
                return 'EPTEST001' === $model->exitProcessId;
            }))
            ->willReturn($startedExit);
        $this->domainEvents
            ->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(DisciplinarySanctionAppliedEvent::class));
        $this->em->expects($this->once())->method('flush');

        $result = $this->manager->applyFrom(new ApplyDisciplinarySanctionModel('DSTEST001'));

        self::assertSame(DisciplinaryCaseConstants::STATUS_SANCTION_APPLIED, $result->getStatus());
        self::assertSame($startedExit, $result->getExitProcess());
        self::assertSame(ExitProcessConstants::STATUS_IN_PROGRESS, $result->getExitProcess()->getStatus());
    }

    public function testApplyWarnAttachesOptionalFile(): void
    {
        $scale = $this->createWarnScale();
        $case = (new DisciplinaryCase())
            ->setEmployee('EMTEST001')
            ->setSanctionScale($scale)
            ->setFacts('Faits')
            ->setOccurredAt(new \DateTimeImmutable('2026-07-01'))
            ->setStatus(DisciplinaryCaseConstants::STATUS_DECISION_PENDING);
        $this->setEntityId($case, 'DSTEST001');

        $tmp = tempnam(sys_get_temp_dir(), 'warn_');
        self::assertNotFalse($tmp);
        file_put_contents($tmp, 'lettre avertissement');
        $file = new UploadedFile($tmp, 'avertissement.pdf', 'application/pdf', null, true);

        $this->em->method('find')->willReturn($case);
        $this->em->expects($this->once())->method('persist')->with(self::isInstanceOf(Document::class));
        $this->em->expects($this->once())->method('flush');

        $result = $this->manager->applyFrom(new ApplyDisciplinarySanctionModel('DSTEST001', $file));

        self::assertNotNull($result->getDocument());
        self::assertSame($file, $result->getDocument()->getFile());
    }

    public function testApplyRejectsFileForNonWarningSanction(): void
    {
        $scale = (new SanctionScale())
            ->setCode(SanctionScaleConstants::CODE_SUSPEND)
            ->setLabel('Mise à pied disciplinaire')
            ->setSeverityLevel(4)
            ->setRequiresHearing(true)
            ->setActive(true);
        $this->setEntityId($scale, 'SSTEST003');

        $case = (new DisciplinaryCase())
            ->setEmployee('EMTEST001')
            ->setSanctionScale($scale)
            ->setFacts('Faits')
            ->setOccurredAt(new \DateTimeImmutable('2026-07-01'))
            ->setStatus(DisciplinaryCaseConstants::STATUS_DECISION_PENDING);
        $this->setEntityId($case, 'DSTEST001');

        $tmp = tempnam(sys_get_temp_dir(), 'warn_');
        self::assertNotFalse($tmp);
        file_put_contents($tmp, 'x');
        $file = new UploadedFile($tmp, 'x.pdf', 'application/pdf', null, true);

        $this->em->method('find')->willReturn($case);
        $this->expectException(InvalidActionInputException::class);

        $this->manager->applyFrom(new ApplyDisciplinarySanctionModel('DSTEST001', $file));
    }

    public function testDecideFromOpenedRejectedWhenHearingRequired(): void
    {
        $scale = (new SanctionScale())
            ->setCode(SanctionScaleConstants::CODE_DISMISS)
            ->setLabel('Licenciement pour faute')
            ->setSeverityLevel(5)
            ->setRequiresHearing(true)
            ->setActive(true);
        $this->setEntityId($scale, 'SSTEST004');

        $case = (new DisciplinaryCase())
            ->setEmployee('EMTEST001')
            ->setSanctionScale($scale)
            ->setFacts('Faits')
            ->setOccurredAt(new \DateTimeImmutable('2026-07-01'))
            ->setStatus(DisciplinaryCaseConstants::STATUS_OPENED);
        $this->setEntityId($case, 'DSTEST001');

        $this->em->method('find')->willReturn($case);

        $this->expectException(InvalidActionInputException::class);

        $this->manager->decideFrom(new DecideDisciplinaryCaseModel('DSTEST001'));
    }

    private function createWarnScale(): SanctionScale
    {
        $scale = (new SanctionScale())
            ->setCode(SanctionScaleConstants::CODE_WARN)
            ->setLabel('Avertissement')
            ->setSeverityLevel(2)
            ->setRequiresHearing(false)
            ->setActive(true);
        $this->setEntityId($scale, 'SSTEST001');

        return $scale;
    }
}
