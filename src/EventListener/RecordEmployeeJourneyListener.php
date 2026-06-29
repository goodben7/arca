<?php

namespace App\EventListener;

use App\Enum\EntityType;
use App\Event\Domain\ApplicationHiredEvent;
use App\Event\Domain\ContractActivatedEvent;
use App\Event\Domain\EmployeeActivatedEvent;
use App\Event\Domain\EmployeeCreatedEvent;
use App\Event\Domain\EmployeeSkillLevelUpgradedEvent;
use App\Event\Domain\EmployeeSkillValidatedEvent;
use App\Event\Domain\EmployeeTerminatedEvent;
use App\Event\Domain\ExitProcessCompletedEvent;
use App\Event\Domain\ExitProcessStartedEvent;
use App\Event\Domain\LeaveRequestApprovedEvent;
use App\Event\Domain\MobilityImplementedEvent;
use App\Event\Domain\OnboardingCompletedEvent;
use App\Event\Domain\OnboardingStartedEvent;
use App\Event\Domain\TrainingEnrollmentCertifiedEvent;
use App\Event\Domain\TrainingEnrollmentCompletedEvent;
use App\Model\JourneyEventTypeConstants;
use App\Model\MobilityRequestConstants;
use App\Model\JourneyStageConstants;
use App\Service\EmployeeJourneyRecorder;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: EmployeeCreatedEvent::class, method: 'onEmployeeCreated')]
#[AsEventListener(event: EmployeeActivatedEvent::class, method: 'onEmployeeActivated')]
#[AsEventListener(event: EmployeeTerminatedEvent::class, method: 'onEmployeeTerminated')]
#[AsEventListener(event: ApplicationHiredEvent::class, method: 'onApplicationHired')]
#[AsEventListener(event: ContractActivatedEvent::class, method: 'onContractActivated')]
#[AsEventListener(event: LeaveRequestApprovedEvent::class, method: 'onLeaveRequestApproved')]
#[AsEventListener(event: EmployeeSkillValidatedEvent::class, method: 'onEmployeeSkillValidated')]
#[AsEventListener(event: EmployeeSkillLevelUpgradedEvent::class, method: 'onEmployeeSkillLevelUpgraded')]
#[AsEventListener(event: OnboardingStartedEvent::class, method: 'onOnboardingStarted')]
#[AsEventListener(event: OnboardingCompletedEvent::class, method: 'onOnboardingCompleted')]
#[AsEventListener(event: TrainingEnrollmentCompletedEvent::class, method: 'onTrainingEnrollmentCompleted')]
#[AsEventListener(event: TrainingEnrollmentCertifiedEvent::class, method: 'onTrainingEnrollmentCertified')]
#[AsEventListener(event: MobilityImplementedEvent::class, method: 'onMobilityImplemented')]
#[AsEventListener(event: ExitProcessStartedEvent::class, method: 'onExitProcessStarted')]
#[AsEventListener(event: ExitProcessCompletedEvent::class, method: 'onExitProcessCompleted')]
class RecordEmployeeJourneyListener
{
    public function __construct(
        private EmployeeJourneyRecorder $journeyRecorder,
    ) {
    }

    public function onEmployeeCreated(EmployeeCreatedEvent $event): void
    {
        $employee = $event->getEmployee();

        $this->journeyRecorder->record(
            employee: $employee,
            stage: JourneyStageConstants::ONBOARDING,
            eventType: JourneyEventTypeConstants::CREATED,
            sourceEntityType: EntityType::EMPLOYEE,
            sourceEntityId: $employee->getId(),
            description: 'employee created',
            actorId: $event->getActorId(),
            occurredAt: $event->getOccurredAt(),
        );

        $jobRole = $employee->getJobRole();
        if (null === $jobRole) {
            return;
        }

        $metadata = [
            'jobRoleId' => $jobRole->getId(),
            'jobRoleCode' => $jobRole->getCode(),
        ];

        $grade = $employee->getGrade();
        if (null !== $grade) {
            $metadata['gradeId'] = $grade->getId();
            $metadata['gradeCode'] = $grade->getCode();
        }

        $this->journeyRecorder->record(
            employee: $employee,
            stage: JourneyStageConstants::ONBOARDING,
            eventType: JourneyEventTypeConstants::JOB_ROLE_ASSIGNED,
            sourceEntityType: EntityType::JOB_ROLE,
            sourceEntityId: $jobRole->getId(),
            metadata: $metadata,
            description: 'job role assigned',
            actorId: $event->getActorId(),
            occurredAt: $event->getOccurredAt(),
        );
    }

    public function onEmployeeActivated(EmployeeActivatedEvent $event): void
    {
        $employee = $event->getEmployee();
        $metadata = null !== $event->getPreviousStatus()
            ? ['previousStatus' => $event->getPreviousStatus()]
            : null;

        $this->journeyRecorder->record(
            employee: $employee,
            stage: JourneyStageConstants::ACTIVE,
            eventType: JourneyEventTypeConstants::ACTIVATED,
            sourceEntityType: EntityType::EMPLOYEE,
            sourceEntityId: $employee->getId(),
            metadata: $metadata,
            description: 'employee activated',
            actorId: $event->getActorId(),
            occurredAt: $event->getOccurredAt(),
        );
    }

    public function onEmployeeTerminated(EmployeeTerminatedEvent $event): void
    {
        $employee = $event->getEmployee();

        $this->journeyRecorder->record(
            employee: $employee,
            stage: JourneyStageConstants::OFFBOARDING,
            eventType: JourneyEventTypeConstants::TERMINATED,
            sourceEntityType: EntityType::EMPLOYEE,
            sourceEntityId: $employee->getId(),
            description: 'employee terminated',
            actorId: $event->getActorId(),
            occurredAt: $event->getOccurredAt(),
        );
    }

    public function onApplicationHired(ApplicationHiredEvent $event): void
    {
        $this->journeyRecorder->record(
            employee: $event->getEmployee(),
            stage: JourneyStageConstants::ONBOARDING,
            eventType: JourneyEventTypeConstants::HIRED,
            sourceEntityType: EntityType::APPLICATION,
            sourceEntityId: $event->getApplication()->getId(),
            metadata: ['previousStage' => JourneyStageConstants::CANDIDATE],
            description: 'candidate hired',
            actorId: $event->getActorId(),
            occurredAt: $event->getOccurredAt(),
        );
    }

    public function onContractActivated(ContractActivatedEvent $event): void
    {
        $contract = $event->getContract();

        $this->journeyRecorder->record(
            employee: $contract->getEmployee(),
            stage: JourneyStageConstants::ACTIVE,
            eventType: JourneyEventTypeConstants::CONTRACT_ACTIVATED,
            sourceEntityType: EntityType::CONTRACT,
            sourceEntityId: $contract->getId(),
            description: 'contract activated',
            actorId: $event->getActorId(),
            occurredAt: $event->getOccurredAt(),
        );
    }

    public function onLeaveRequestApproved(LeaveRequestApprovedEvent $event): void
    {
        $leaveRequest = $event->getLeaveRequest();

        $this->journeyRecorder->record(
            employee: $leaveRequest->getEmployee(),
            stage: JourneyStageConstants::LEAVE,
            eventType: JourneyEventTypeConstants::LEAVE_APPROVED,
            sourceEntityType: EntityType::LEAVE_REQUEST,
            sourceEntityId: $leaveRequest->getId(),
            description: 'leave request approved',
            actorId: $event->getActorId(),
            occurredAt: $event->getOccurredAt(),
        );
    }

    public function onEmployeeSkillValidated(EmployeeSkillValidatedEvent $event): void
    {
        $employeeSkill = $event->getEmployeeSkill();
        $skill = $employeeSkill->getSkill();

        $this->journeyRecorder->record(
            employee: $employeeSkill->getEmployee(),
            stage: JourneyStageConstants::TRAINING,
            eventType: JourneyEventTypeConstants::SKILL_VALIDATED,
            sourceEntityType: EntityType::EMPLOYEE_SKILL,
            sourceEntityId: $employeeSkill->getId(),
            metadata: [
                'skillId' => $skill?->getId(),
                'skillCode' => $skill?->getCode(),
                'level' => $employeeSkill->getLevel(),
            ],
            description: 'employee skill validated',
            actorId: $event->getActorId(),
            occurredAt: $event->getOccurredAt(),
        );
    }

    public function onEmployeeSkillLevelUpgraded(EmployeeSkillLevelUpgradedEvent $event): void
    {
        $employeeSkill = $event->getEmployeeSkill();
        $skill = $employeeSkill->getSkill();

        $this->journeyRecorder->record(
            employee: $employeeSkill->getEmployee(),
            stage: JourneyStageConstants::TRAINING,
            eventType: JourneyEventTypeConstants::SKILL_LEVEL_UPGRADED,
            sourceEntityType: EntityType::EMPLOYEE_SKILL,
            sourceEntityId: $employeeSkill->getId(),
            metadata: [
                'skillId' => $skill?->getId(),
                'skillCode' => $skill?->getCode(),
                'previousLevel' => $event->getPreviousLevel(),
                'newLevel' => $employeeSkill->getLevel(),
            ],
            description: 'employee skill level upgraded',
            actorId: $event->getActorId(),
            occurredAt: $event->getOccurredAt(),
        );
    }

    public function onOnboardingStarted(OnboardingStartedEvent $event): void
    {
        $process = $event->getProcess();

        $this->journeyRecorder->record(
            employee: $process->getEmployee(),
            stage: JourneyStageConstants::ONBOARDING,
            eventType: JourneyEventTypeConstants::ONBOARDING_STARTED,
            sourceEntityType: EntityType::ONBOARDING_PROCESS,
            sourceEntityId: $process->getId(),
            description: 'onboarding started',
            actorId: $event->getActorId(),
            occurredAt: $event->getOccurredAt(),
        );
    }

    public function onOnboardingCompleted(OnboardingCompletedEvent $event): void
    {
        $process = $event->getProcess();

        $this->journeyRecorder->record(
            employee: $process->getEmployee(),
            stage: JourneyStageConstants::ONBOARDING,
            eventType: JourneyEventTypeConstants::ONBOARDING_COMPLETED,
            sourceEntityType: EntityType::ONBOARDING_PROCESS,
            sourceEntityId: $process->getId(),
            description: 'onboarding completed',
            actorId: $event->getActorId(),
            occurredAt: $event->getOccurredAt(),
        );
    }

    public function onTrainingEnrollmentCompleted(TrainingEnrollmentCompletedEvent $event): void
    {
        $enrollment = $event->getEnrollment();

        $this->journeyRecorder->record(
            employee: $enrollment->getEmployee(),
            stage: JourneyStageConstants::TRAINING,
            eventType: JourneyEventTypeConstants::TRAINING_COMPLETED,
            sourceEntityType: EntityType::TRAINING_ENROLLMENT,
            sourceEntityId: $enrollment->getId(),
            metadata: [
                'trainingSessionId' => $enrollment->getTrainingSession(),
            ],
            description: 'training enrollment completed',
            actorId: $event->getActorId(),
            occurredAt: $event->getOccurredAt(),
        );
    }

    public function onTrainingEnrollmentCertified(TrainingEnrollmentCertifiedEvent $event): void
    {
        $enrollment = $event->getEnrollment();

        $this->journeyRecorder->record(
            employee: $enrollment->getEmployee(),
            stage: JourneyStageConstants::TRAINING,
            eventType: JourneyEventTypeConstants::TRAINING_CERTIFIED,
            sourceEntityType: EntityType::TRAINING_ENROLLMENT,
            sourceEntityId: $enrollment->getId(),
            metadata: [
                'trainingSessionId' => $enrollment->getTrainingSession(),
                'score' => $enrollment->getScore(),
                'certificate' => $enrollment->getCertificate(),
            ],
            description: 'training enrollment certified',
            actorId: $event->getActorId(),
            occurredAt: $event->getOccurredAt(),
        );
    }

    public function onMobilityImplemented(MobilityImplementedEvent $event): void
    {
        $request = $event->getMobilityRequest();
        $type = (string) $request->getType();

        $metadata = [
            'mobilityType' => $type,
            'targetDepartment' => $request->getTargetDepartment(),
        ];

        $targetJobRole = $request->getTargetJobRole();
        if (null !== $targetJobRole) {
            $metadata['targetJobRoleId'] = $targetJobRole->getId();
            $metadata['targetJobRoleCode'] = $targetJobRole->getCode();
        }

        $targetGrade = $request->getTargetGrade();
        if (null !== $targetGrade) {
            $metadata['targetGradeId'] = $targetGrade->getId();
            $metadata['targetGradeCode'] = $targetGrade->getCode();
        }

        if (\in_array($type, [MobilityRequestConstants::TYPE_TRANSFER, MobilityRequestConstants::TYPE_SECONDMENT], true)) {
            $this->journeyRecorder->record(
                employee: (string) $request->getEmployee(),
                stage: JourneyStageConstants::TRANSFER,
                eventType: JourneyEventTypeConstants::TRANSFERRED,
                sourceEntityType: EntityType::MOBILITY_REQUEST,
                sourceEntityId: $request->getId(),
                metadata: $metadata,
                description: 'employee transferred',
                actorId: $event->getActorId(),
                occurredAt: $event->getOccurredAt(),
            );

            return;
        }

        $this->journeyRecorder->record(
            employee: (string) $request->getEmployee(),
            stage: JourneyStageConstants::PROMOTION,
            eventType: JourneyEventTypeConstants::PROMOTED,
            sourceEntityType: EntityType::MOBILITY_REQUEST,
            sourceEntityId: $request->getId(),
            metadata: $metadata,
            description: MobilityRequestConstants::TYPE_DEMOTION === $type ? 'employee demoted' : 'employee promoted',
            actorId: $event->getActorId(),
            occurredAt: $event->getOccurredAt(),
        );
    }

    public function onExitProcessStarted(ExitProcessStartedEvent $event): void
    {
        $process = $event->getProcess();

        $this->journeyRecorder->record(
            employee: (string) $process->getEmployee(),
            stage: JourneyStageConstants::OFFBOARDING,
            eventType: JourneyEventTypeConstants::OFFBOARDING_STARTED,
            sourceEntityType: EntityType::EXIT_PROCESS,
            sourceEntityId: $process->getId(),
            metadata: [
                'reason' => $process->getReason(),
                'departureDate' => $process->getDepartureDate()?->format('Y-m-d'),
            ],
            description: 'offboarding started',
            actorId: $event->getActorId(),
            occurredAt: $event->getOccurredAt(),
        );
    }

    public function onExitProcessCompleted(ExitProcessCompletedEvent $event): void
    {
        $process = $event->getProcess();

        $this->journeyRecorder->record(
            employee: (string) $process->getEmployee(),
            stage: JourneyStageConstants::ARCHIVED,
            eventType: JourneyEventTypeConstants::ARCHIVED,
            sourceEntityType: EntityType::EXIT_PROCESS,
            sourceEntityId: $process->getId(),
            metadata: [
                'reason' => $process->getReason(),
                'departureDate' => $process->getDepartureDate()?->format('Y-m-d'),
            ],
            description: 'employee archived after exit process',
            actorId: $event->getActorId(),
            occurredAt: $event->getOccurredAt(),
        );
    }
}
