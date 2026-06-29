<?php

namespace App\Tests\Unit\EventListener;

use App\Entity\Application;
use App\Entity\Contract;
use App\Entity\Employee;
use App\Entity\EmployeeJourneyEntry;
use App\Entity\Grade;
use App\Entity\JobRole;
use App\Entity\Skill;
use App\Entity\SkillCategory;
use App\Entity\EmployeeSkill;
use App\Entity\LeaveRequest;
use App\Entity\MobilityRequest;
use App\Entity\ExitProcess;
use App\Entity\OnboardingProcess;
use App\Enum\EntityType;
use App\Event\Domain\ApplicationHiredEvent;
use App\Event\Domain\ContractActivatedEvent;
use App\Event\Domain\EmployeeActivatedEvent;
use App\Event\Domain\EmployeeCreatedEvent;
use App\Event\Domain\EmployeeSkillValidatedEvent;
use App\Event\Domain\EmployeeTerminatedEvent;
use App\Event\Domain\LeaveRequestApprovedEvent;
use App\Event\Domain\ExitProcessCompletedEvent;
use App\Event\Domain\ExitProcessStartedEvent;
use App\Event\Domain\MobilityImplementedEvent;
use App\Event\Domain\OnboardingCompletedEvent;
use App\Event\Domain\OnboardingStartedEvent;
use App\EventListener\RecordEmployeeJourneyListener;
use App\Model\ContractConstants;
use App\Model\EmployeeConstants;
use App\Model\JourneyEventTypeConstants;
use App\Model\JourneyStageConstants;
use App\Model\MobilityRequestConstants;
use App\Model\OnboardingProcessConstants;
use App\Model\SkillConstants;
use App\Service\EmployeeJourneyRecorder;
use App\Tests\Unit\Manager\ManagerTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class RecordEmployeeJourneyListenerTest extends ManagerTestCase
{
    private EmployeeJourneyRecorder&MockObject $journeyRecorder;
    private RecordEmployeeJourneyListener $listener;

    protected function setUp(): void
    {
        $this->journeyRecorder = $this->createMock(EmployeeJourneyRecorder::class);
        $this->listener = new RecordEmployeeJourneyListener($this->journeyRecorder);
    }

    public function testOnEmployeeCreatedRecordsJourneyEntry(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);

        $this->journeyRecorder
            ->expects($this->once())
            ->method('record')
            ->with(
                $employee,
                JourneyStageConstants::ONBOARDING,
                JourneyEventTypeConstants::CREATED,
                EntityType::EMPLOYEE,
                'EMTEST001',
                null,
                'employee created',
                'SYSTEM',
                $this->isInstanceOf(\DateTimeImmutable::class),
            );

        $this->listener->onEmployeeCreated(new EmployeeCreatedEvent($employee, 'SYSTEM'));
    }

    public function testOnEmployeeCreatedRecordsJobRoleAssignedWhenJobRolePresent(): void
    {
        $grade = (new Grade())->setCode('G2')->setName('Grade 2')->setRank(2);
        $this->setEntityId($grade, 'GRTEST001');

        $jobRole = (new JobRole())
            ->setCode('ACC')
            ->setTitle('Comptable')
            ->setGrade($grade);
        $this->setEntityId($jobRole, 'JRTEST001');

        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);
        $employee->setJobRole($jobRole)->setGrade($grade);

        $this->journeyRecorder
            ->expects($this->exactly(2))
            ->method('record')
            ->willReturnCallback(function (
                $employeeArg,
                string $stage,
                string $eventType,
                ?string $sourceEntityType,
                ?string $sourceEntityId,
                ?array $metadata,
            ) use ($employee, $jobRole, $grade): EmployeeJourneyEntry {
                if (JourneyEventTypeConstants::CREATED === $eventType) {
                    self::assertSame($employee, $employeeArg);
                    self::assertSame(JourneyStageConstants::ONBOARDING, $stage);
                    self::assertSame(EntityType::EMPLOYEE, $sourceEntityType);
                    self::assertSame('EMTEST001', $sourceEntityId);

                    return new EmployeeJourneyEntry();
                }

                self::assertSame(JourneyEventTypeConstants::JOB_ROLE_ASSIGNED, $eventType);
                self::assertSame($employee, $employeeArg);
                self::assertSame(JourneyStageConstants::ONBOARDING, $stage);
                self::assertSame(EntityType::JOB_ROLE, $sourceEntityType);
                self::assertSame('JRTEST001', $sourceEntityId);
                self::assertSame([
                    'jobRoleId' => 'JRTEST001',
                    'jobRoleCode' => 'ACC',
                    'gradeId' => 'GRTEST001',
                    'gradeCode' => 'G2',
                ], $metadata);

                return new EmployeeJourneyEntry();
            });

        $this->listener->onEmployeeCreated(new EmployeeCreatedEvent($employee, 'SYSTEM'));
    }

    public function testOnEmployeeActivatedRecordsPreviousStatus(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);

        $this->journeyRecorder
            ->expects($this->once())
            ->method('record')
            ->with(
                $employee,
                JourneyStageConstants::ACTIVE,
                JourneyEventTypeConstants::ACTIVATED,
                EntityType::EMPLOYEE,
                'EMTEST001',
                ['previousStatus' => EmployeeConstants::STATUS_INACTIVE],
                'employee activated',
                'SYSTEM',
                $this->isInstanceOf(\DateTimeImmutable::class),
            );

        $this->listener->onEmployeeActivated(
            new EmployeeActivatedEvent($employee, 'SYSTEM', EmployeeConstants::STATUS_INACTIVE)
        );
    }

    public function testOnApplicationHiredRecordsCandidateTransition(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);
        $application = new Application();
        $this->setEntityId($application, 'APTEST001');

        $this->journeyRecorder
            ->expects($this->once())
            ->method('record')
            ->with(
                $employee,
                JourneyStageConstants::ONBOARDING,
                JourneyEventTypeConstants::HIRED,
                EntityType::APPLICATION,
                'APTEST001',
                ['previousStage' => JourneyStageConstants::CANDIDATE],
                'candidate hired',
                'SYSTEM',
                $this->isInstanceOf(\DateTimeImmutable::class),
            );

        $this->listener->onApplicationHired(
            new ApplicationHiredEvent($application, $employee, 'SYSTEM')
        );
    }

    public function testOnContractActivatedRecordsForEmployee(): void
    {
        $contract = $this->createContract('CTTEST001', ContractConstants::STATUS_ACTIVE);

        $this->journeyRecorder
            ->expects($this->once())
            ->method('record')
            ->with(
                'EMTEST001',
                JourneyStageConstants::ACTIVE,
                JourneyEventTypeConstants::CONTRACT_ACTIVATED,
                EntityType::CONTRACT,
                'CTTEST001',
                null,
                'contract activated',
                'SYSTEM',
                $this->isInstanceOf(\DateTimeImmutable::class),
            );

        $this->listener->onContractActivated(new ContractActivatedEvent($contract, 'SYSTEM'));
    }

    public function testOnLeaveRequestApprovedRecordsLeaveStage(): void
    {
        $leaveRequest = (new LeaveRequest())
            ->setEmployee('EMTEST001')
            ->setStatus('APPROVED');

        $this->setEntityId($leaveRequest, 'LRTEST001');

        $this->journeyRecorder
            ->expects($this->once())
            ->method('record')
            ->with(
                'EMTEST001',
                JourneyStageConstants::LEAVE,
                JourneyEventTypeConstants::LEAVE_APPROVED,
                EntityType::LEAVE_REQUEST,
                'LRTEST001',
                null,
                'leave request approved',
                'SYSTEM',
                $this->isInstanceOf(\DateTimeImmutable::class),
            );

        $this->listener->onLeaveRequestApproved(new LeaveRequestApprovedEvent($leaveRequest, 'SYSTEM'));
    }

    public function testOnEmployeeTerminatedRecordsOffboardingStage(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_TERMINATED);

        $this->journeyRecorder
            ->expects($this->once())
            ->method('record')
            ->with(
                $employee,
                JourneyStageConstants::OFFBOARDING,
                JourneyEventTypeConstants::TERMINATED,
                EntityType::EMPLOYEE,
                'EMTEST001',
                null,
                'employee terminated',
                'SYSTEM',
                $this->isInstanceOf(\DateTimeImmutable::class),
            );

        $this->listener->onEmployeeTerminated(new EmployeeTerminatedEvent($employee, 'SYSTEM'));
    }

    public function testOnEmployeeSkillValidatedRecordsTrainingStage(): void
    {
        $category = (new SkillCategory())->setCode('FIN')->setName('Finance');
        $this->setEntityId($category, 'SKCTEST001');

        $skill = (new Skill())->setCode('EXCEL')->setName('Excel')->setCategory($category);
        $this->setEntityId($skill, 'SKTEST001');

        $employeeSkill = (new EmployeeSkill())
            ->setEmployee('EMTEST001')
            ->setSkill($skill)
            ->setLevel(SkillConstants::LEVEL_INTERMEDIATE)
            ->setValidatedAt(new \DateTimeImmutable());
        $this->setEntityId($employeeSkill, 'ESTEST001');

        $this->journeyRecorder
            ->expects($this->once())
            ->method('record')
            ->with(
                'EMTEST001',
                JourneyStageConstants::TRAINING,
                JourneyEventTypeConstants::SKILL_VALIDATED,
                EntityType::EMPLOYEE_SKILL,
                'ESTEST001',
                [
                    'skillId' => 'SKTEST001',
                    'skillCode' => 'EXCEL',
                    'level' => SkillConstants::LEVEL_INTERMEDIATE,
                ],
                'employee skill validated',
                'SYSTEM',
                $this->isInstanceOf(\DateTimeImmutable::class),
            )
            ->willReturn(new EmployeeJourneyEntry());

        $this->listener->onEmployeeSkillValidated(new EmployeeSkillValidatedEvent($employeeSkill, 'SYSTEM'));
    }

    public function testOnOnboardingStartedRecordsOnboardingStage(): void
    {
        $process = (new OnboardingProcess())
            ->setEmployee('EMTEST001')
            ->setStatus(OnboardingProcessConstants::STATUS_IN_PROGRESS)
            ->setStartedAt(new \DateTimeImmutable());
        $this->setEntityId($process, 'OPTEST001');

        $this->journeyRecorder
            ->expects($this->once())
            ->method('record')
            ->with(
                'EMTEST001',
                JourneyStageConstants::ONBOARDING,
                JourneyEventTypeConstants::ONBOARDING_STARTED,
                EntityType::ONBOARDING_PROCESS,
                'OPTEST001',
                null,
                'onboarding started',
                'SYSTEM',
                $this->isInstanceOf(\DateTimeImmutable::class),
            );

        $this->listener->onOnboardingStarted(new OnboardingStartedEvent($process, 'SYSTEM'));
    }

    public function testOnOnboardingCompletedRecordsOnboardingStage(): void
    {
        $process = (new OnboardingProcess())
            ->setEmployee('EMTEST001')
            ->setStatus(OnboardingProcessConstants::STATUS_COMPLETED)
            ->setCompletedAt(new \DateTimeImmutable());
        $this->setEntityId($process, 'OPTEST001');

        $this->journeyRecorder
            ->expects($this->once())
            ->method('record')
            ->with(
                'EMTEST001',
                JourneyStageConstants::ONBOARDING,
                JourneyEventTypeConstants::ONBOARDING_COMPLETED,
                EntityType::ONBOARDING_PROCESS,
                'OPTEST001',
                null,
                'onboarding completed',
                'SYSTEM',
                $this->isInstanceOf(\DateTimeImmutable::class),
            );

        $this->listener->onOnboardingCompleted(new OnboardingCompletedEvent($process, 'SYSTEM'));
    }

    public function testOnMobilityImplementedRecordsTransferredForTransfer(): void
    {
        $request = $this->createMobilityRequest(
            MobilityRequestConstants::TYPE_TRANSFER,
            'Finance',
        );

        $this->journeyRecorder
            ->expects($this->once())
            ->method('record')
            ->with(
                'EMTEST001',
                JourneyStageConstants::TRANSFER,
                JourneyEventTypeConstants::TRANSFERRED,
                EntityType::MOBILITY_REQUEST,
                'MBTEST001',
                [
                    'mobilityType' => MobilityRequestConstants::TYPE_TRANSFER,
                    'targetDepartment' => 'Finance',
                    'targetJobRoleId' => 'JRTEST002',
                    'targetJobRoleCode' => 'SR_ACC',
                    'targetGradeId' => 'GRTEST002',
                    'targetGradeCode' => 'G4',
                ],
                'employee transferred',
                'SYSTEM',
                $this->isInstanceOf(\DateTimeImmutable::class),
            );

        $this->listener->onMobilityImplemented(new MobilityImplementedEvent($request, 'SYSTEM'));
    }

    public function testOnMobilityImplementedRecordsTransferredForSecondment(): void
    {
        $request = $this->createMobilityRequest(
            MobilityRequestConstants::TYPE_SECONDMENT,
            'Projets',
        );

        $this->journeyRecorder
            ->expects($this->once())
            ->method('record')
            ->with(
                'EMTEST001',
                JourneyStageConstants::TRANSFER,
                JourneyEventTypeConstants::TRANSFERRED,
                EntityType::MOBILITY_REQUEST,
                'MBTEST001',
                $this->callback(fn (array $metadata) => MobilityRequestConstants::TYPE_SECONDMENT === $metadata['mobilityType']),
                'employee transferred',
                'SYSTEM',
                $this->isInstanceOf(\DateTimeImmutable::class),
            );

        $this->listener->onMobilityImplemented(new MobilityImplementedEvent($request, 'SYSTEM'));
    }

    public function testOnMobilityImplementedRecordsPromotedForPromotion(): void
    {
        $request = $this->createMobilityRequest(
            MobilityRequestConstants::TYPE_PROMOTION,
            'Finance',
        );

        $this->journeyRecorder
            ->expects($this->once())
            ->method('record')
            ->with(
                'EMTEST001',
                JourneyStageConstants::PROMOTION,
                JourneyEventTypeConstants::PROMOTED,
                EntityType::MOBILITY_REQUEST,
                'MBTEST001',
                $this->isArray(),
                'employee promoted',
                'SYSTEM',
                $this->isInstanceOf(\DateTimeImmutable::class),
            );

        $this->listener->onMobilityImplemented(new MobilityImplementedEvent($request, 'SYSTEM'));
    }

    public function testOnMobilityImplementedRecordsPromotedForDemotion(): void
    {
        $request = $this->createMobilityRequest(
            MobilityRequestConstants::TYPE_DEMOTION,
            null,
        );

        $this->journeyRecorder
            ->expects($this->once())
            ->method('record')
            ->with(
                'EMTEST001',
                JourneyStageConstants::PROMOTION,
                JourneyEventTypeConstants::PROMOTED,
                EntityType::MOBILITY_REQUEST,
                'MBTEST001',
                $this->callback(fn (array $metadata) => MobilityRequestConstants::TYPE_DEMOTION === $metadata['mobilityType']),
                'employee demoted',
                'SYSTEM',
                $this->isInstanceOf(\DateTimeImmutable::class),
            );

        $this->listener->onMobilityImplemented(new MobilityImplementedEvent($request, 'SYSTEM'));
    }

    public function testOnExitProcessStartedRecordsOffboardingStage(): void
    {
        $process = (new ExitProcess())
            ->setEmployee('EMTEST001')
            ->setReason('RESIGNATION')
            ->setDepartureDate(new \DateTimeImmutable('2026-08-01'))
            ->setStatus('IN_PROGRESS');
        $this->setEntityId($process, 'EPTEST001');

        $this->journeyRecorder
            ->expects($this->once())
            ->method('record')
            ->with(
                'EMTEST001',
                JourneyStageConstants::OFFBOARDING,
                JourneyEventTypeConstants::OFFBOARDING_STARTED,
                EntityType::EXIT_PROCESS,
                'EPTEST001',
                self::isArray(),
                'offboarding started',
                'SYSTEM',
                $this->isInstanceOf(\DateTimeImmutable::class),
            );

        $this->listener->onExitProcessStarted(new ExitProcessStartedEvent($process, 'SYSTEM'));
    }

    public function testOnExitProcessCompletedRecordsArchivedStage(): void
    {
        $process = (new ExitProcess())
            ->setEmployee('EMTEST001')
            ->setReason('RETIREMENT')
            ->setDepartureDate(new \DateTimeImmutable('2026-08-01'))
            ->setStatus('COMPLETED');
        $this->setEntityId($process, 'EPTEST001');

        $this->journeyRecorder
            ->expects($this->once())
            ->method('record')
            ->with(
                'EMTEST001',
                JourneyStageConstants::ARCHIVED,
                JourneyEventTypeConstants::ARCHIVED,
                EntityType::EXIT_PROCESS,
                'EPTEST001',
                self::isArray(),
                'employee archived after exit process',
                'SYSTEM',
                $this->isInstanceOf(\DateTimeImmutable::class),
            );

        $this->listener->onExitProcessCompleted(new ExitProcessCompletedEvent($process, 'SYSTEM'));
    }

    private function createMobilityRequest(string $type, ?string $targetDepartment): MobilityRequest
    {
        $family = $this->createJobFamily('JFTEST001', 'FIN');
        $targetGrade = $this->createGrade('GRTEST002', 'G4', 4);
        $targetRole = $this->createJobRole('JRTEST002', 'SR_ACC', $family, $targetGrade);

        $request = (new MobilityRequest())
            ->setEmployee('EMTEST001')
            ->setType($type)
            ->setTargetJobRole($targetRole)
            ->setTargetGrade($targetGrade)
            ->setTargetDepartment($targetDepartment);
        $this->setEntityId($request, 'MBTEST001');

        return $request;
    }
}
