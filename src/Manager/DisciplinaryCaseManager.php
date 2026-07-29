<?php

namespace App\Manager;

use App\Entity\DisciplinaryCase;
use App\Entity\Document;
use App\Entity\Employee;
use App\Entity\ExitProcess;
use App\Entity\SanctionScale;
use App\Entity\User;
use App\Enum\EntityType;
use App\Event\ActivityEvent;
use App\Event\Domain\DisciplinaryCaseOpenedEvent;
use App\Event\Domain\DisciplinarySanctionAppliedEvent;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Message\Query\GetUserDetails;
use App\Message\Query\QueryBusInterface;
use App\Model\ApplyDisciplinarySanctionModel;
use App\Model\CancelDisciplinaryCaseModel;
use App\Model\CloseDisciplinaryCaseModel;
use App\Model\DecideDisciplinaryCaseModel;
use App\Model\DisciplinaryCaseConstants;
use App\Model\EmployeeConstants;
use App\Model\ExitProcessConstants;
use App\Model\NewDisciplinaryCaseModel;
use App\Model\NewExitProcessModel;
use App\Model\OpenDisciplinaryCaseModel;
use App\Model\RejectDisciplinaryCaseModel;
use App\Model\SanctionScaleConstants;
use App\Model\ScheduleDisciplinaryHearingModel;
use App\Model\StartExitProcessModel;
use App\Model\SuspendEmployeeModel;
use App\Repository\DisciplinaryCaseRepository;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DisciplinaryCaseManager
{
    private const array ELIGIBLE_EMPLOYEE_STATUSES = [
        EmployeeConstants::STATUS_ACTIVE,
        EmployeeConstants::STATUS_ON_LEAVE,
        EmployeeConstants::STATUS_PROBATION,
    ];

    public function __construct(
        private EntityManagerInterface $em,
        private ActivityEventDispatcher $eventDispatcher,
        private Security $security,
        private QueryBusInterface $queries,
        private EventDispatcherInterface $domainEventDispatcher,
        private EmployeeManager $employees,
        private ExitProcessManager $exitProcesses,
        private DisciplinaryCaseRepository $disciplinaryCases,
    ) {
    }

    public function createFrom(NewDisciplinaryCaseModel $model): DisciplinaryCase
    {
        $employee = $this->findEmployee((string) $model->employee);

        if (!\in_array($employee->getStatus(), self::ELIGIBLE_EMPLOYEE_STATUSES, true)) {
            throw new InvalidActionInputException(
                'disciplinary case can only be created for an active, on-leave or probation employee'
            );
        }

        $scale = $this->findSanctionScale((string) $model->sanctionScale);
        if (!$scale->isActive()) {
            throw new InvalidActionInputException('sanction scale must be active');
        }

        if (null !== $this->disciplinaryCases->findActiveForEmployee((string) $employee->getId())) {
            throw new InvalidActionInputException('an active disciplinary case already exists for this employee');
        }

        $occurredAt = $model->occurredAt instanceof \DateTimeImmutable
            ? $model->occurredAt
            : \DateTimeImmutable::createFromInterface($model->occurredAt);

        $case = (new DisciplinaryCase())
            ->setEmployee((string) $employee->getId())
            ->setSanctionScale($scale)
            ->setFacts((string) $model->facts)
            ->setOccurredAt($occurredAt)
            ->setReason($model->reason)
            ->setStatus(DisciplinaryCaseConstants::STATUS_DRAFT);

        $this->em->persist($case);
        $this->em->flush();

        $this->eventDispatcher->dispatch($case, ActivityEvent::ACTION_CREATE);

        return $case;
    }

    public function openFrom(OpenDisciplinaryCaseModel $model): DisciplinaryCase
    {
        $case = $this->findCase($model->disciplinaryCaseId);
        $this->assertActionAllowed($case, DisciplinaryCaseConstants::ACTION_OPEN);

        $actorId = $this->resolveActorId();
        $case
            ->setStatus(DisciplinaryCaseConstants::STATUS_OPENED)
            ->setOpenedAt(new \DateTimeImmutable())
            ->setOpenedBy($actorId);

        $this->em->flush();

        $this->eventDispatcher->dispatch($case, ActivityEvent::ACTION_EDIT, null, 'disciplinary case opened');

        $this->domainEventDispatcher->dispatch(
            new DisciplinaryCaseOpenedEvent($case, $actorId)
        );

        return $case;
    }

    public function scheduleHearingFrom(ScheduleDisciplinaryHearingModel $model): DisciplinaryCase
    {
        $case = $this->findCase($model->disciplinaryCaseId);
        $scale = $case->getSanctionScale();

        if (null !== $scale && !$scale->isRequiresHearing()) {
            throw new InvalidActionInputException(
                'hearing is not required for this sanction scale; use decide instead'
            );
        }

        $this->assertActionAllowed($case, DisciplinaryCaseConstants::ACTION_SCHEDULE_HEARING);

        $hearingAt = $model->hearingAt instanceof \DateTimeImmutable
            ? $model->hearingAt
            : \DateTimeImmutable::createFromInterface($model->hearingAt);

        $actorId = $this->resolveActorId();
        $case
            ->setStatus(DisciplinaryCaseConstants::STATUS_HEARING_SCHEDULED)
            ->setHearingAt($hearingAt)
            ->setHearingBy($actorId);

        $this->em->flush();

        $this->eventDispatcher->dispatch($case, ActivityEvent::ACTION_EDIT, null, 'disciplinary hearing scheduled');

        return $case;
    }

    public function decideFrom(DecideDisciplinaryCaseModel $model): DisciplinaryCase
    {
        $case = $this->findCase($model->disciplinaryCaseId);
        $this->assertActionAllowed($case, DisciplinaryCaseConstants::ACTION_DECIDE);

        $actorId = $this->resolveActorId();
        $case
            ->setStatus(DisciplinaryCaseConstants::STATUS_DECISION_PENDING)
            ->setDecidedAt(new \DateTimeImmutable())
            ->setDecidedBy($actorId);

        if (null !== $model->reason) {
            $case->setReason($model->reason);
        }

        $this->em->flush();

        $this->eventDispatcher->dispatch($case, ActivityEvent::ACTION_EDIT, null, 'disciplinary case decided');

        return $case;
    }

    public function applyFrom(ApplyDisciplinarySanctionModel $model): DisciplinaryCase
    {
        $case = $this->findCase($model->disciplinaryCaseId);
        $this->assertActionAllowed($case, DisciplinaryCaseConstants::ACTION_APPLY);

        $actorId = $this->resolveActorId();
        $scale = $case->getSanctionScale();

        if (null !== $scale) {
            $code = $scale->getCode();

            if (\in_array($code, [SanctionScaleConstants::CODE_WARN, SanctionScaleConstants::CODE_BLAME], true)) {
                $document = (new Document())
                    ->setType(Document::TYPE_WARNING_LETTER)
                    ->setTitle(sprintf('Sanction disciplinaire — %s', $scale->getLabel()))
                    ->setHolderType(EntityType::EMPLOYEE)
                    ->setHolderId((string) $case->getEmployee())
                    ->setDocumentRefNumber((string) $case->getId());
                if (null !== $model->file) {
                    $document->setFile($model->file);
                }
                $this->em->persist($document);
                $case->setDocument($document);
            } elseif (null !== $model->file) {
                throw new InvalidActionInputException(
                    'warning letter file is only allowed for WARN or BLAME sanctions'
                );
            } elseif (SanctionScaleConstants::CODE_SUSPEND === $code) {
                $this->employees->suspendFrom(new SuspendEmployeeModel((string) $case->getEmployee()));
            } elseif (SanctionScaleConstants::CODE_DISMISS === $code) {
                $exit = $this->exitProcesses->createFrom(new NewExitProcessModel(
                    (string) $case->getEmployee(),
                    ExitProcessConstants::REASON_DISMISSAL,
                    new \DateTimeImmutable('today'),
                ));
                $exit = $this->exitProcesses->startFrom(new StartExitProcessModel((string) $exit->getId()));
                $case->setExitProcess($exit);
            }
        }

        $case
            ->setStatus(DisciplinaryCaseConstants::STATUS_SANCTION_APPLIED)
            ->setAppliedAt(new \DateTimeImmutable())
            ->setAppliedBy($actorId);

        $this->em->flush();

        $this->eventDispatcher->dispatch($case, ActivityEvent::ACTION_EDIT, null, 'disciplinary sanction applied');

        $this->domainEventDispatcher->dispatch(
            new DisciplinarySanctionAppliedEvent($case, $actorId)
        );

        return $case;
    }

    public function cancelFrom(CancelDisciplinaryCaseModel $model): DisciplinaryCase
    {
        $case = $this->findCase($model->disciplinaryCaseId);
        $this->assertActionAllowed($case, DisciplinaryCaseConstants::ACTION_CANCEL);

        $actorId = $this->resolveActorId();
        $case
            ->setStatus(DisciplinaryCaseConstants::STATUS_CANCELLED)
            ->setCancelledAt(new \DateTimeImmutable())
            ->setCancelledBy($actorId);

        $this->em->flush();

        $this->eventDispatcher->dispatch($case, ActivityEvent::ACTION_EDIT, null, 'disciplinary case cancelled');

        return $case;
    }

    public function rejectFrom(RejectDisciplinaryCaseModel $model): DisciplinaryCase
    {
        $case = $this->findCase($model->disciplinaryCaseId);
        $this->assertActionAllowed($case, DisciplinaryCaseConstants::ACTION_REJECT);

        $actorId = $this->resolveActorId();
        $case
            ->setStatus(DisciplinaryCaseConstants::STATUS_REJECTED)
            ->setRejectedAt(new \DateTimeImmutable())
            ->setRejectedBy($actorId)
            ->setRejectionReason((string) $model->reason);

        $this->em->flush();

        $this->eventDispatcher->dispatch($case, ActivityEvent::ACTION_EDIT, null, 'disciplinary case rejected');

        return $case;
    }

    public function closeFrom(CloseDisciplinaryCaseModel $model): DisciplinaryCase
    {
        $case = $this->findCase($model->disciplinaryCaseId);
        $this->assertActionAllowed($case, DisciplinaryCaseConstants::ACTION_CLOSE);

        $actorId = $this->resolveActorId();
        $case
            ->setStatus(DisciplinaryCaseConstants::STATUS_CLOSED)
            ->setClosedAt(new \DateTimeImmutable())
            ->setClosedBy($actorId);

        $this->em->flush();

        $this->eventDispatcher->dispatch($case, ActivityEvent::ACTION_EDIT, null, 'disciplinary case closed');

        return $case;
    }

    private function assertActionAllowed(DisciplinaryCase $case, string $action): void
    {
        $requiresHearing = $case->getSanctionScale()?->isRequiresHearing();
        $allowed = DisciplinaryCaseConstants::getAllowedActionsForStatus($case->getStatus(), $requiresHearing);
        if (!\in_array($action, $allowed, true)) {
            throw new InvalidActionInputException('Action not allowed : invalid disciplinary case state');
        }
    }

    private function findCase(?string $caseId): DisciplinaryCase
    {
        if (!$caseId) {
            throw new InvalidActionInputException('disciplinaryCaseId is required');
        }

        $case = $this->em->find(DisciplinaryCase::class, $caseId);

        if (null === $case) {
            throw new UnavailableDataException(sprintf('cannot find disciplinary case with id: %s', $caseId));
        }

        return $case;
    }

    private function findEmployee(string $employeeId): Employee
    {
        $employee = $this->em->find(Employee::class, $employeeId);

        if (null === $employee) {
            throw new UnavailableDataException(sprintf('cannot find employee with id: %s', $employeeId));
        }

        return $employee;
    }

    private function findSanctionScale(string $scaleId): SanctionScale
    {
        $scale = $this->em->find(SanctionScale::class, $scaleId);

        if (null === $scale) {
            throw new UnavailableDataException(sprintf('cannot find sanction scale with id: %s', $scaleId));
        }

        return $scale;
    }

    private function resolveActorId(): string
    {
        $identifier = $this->security->getUser()?->getUserIdentifier();
        if (!$identifier) {
            return 'SYSTEM';
        }

        /** @var User|null $user */
        $user = $this->queries->ask(new GetUserDetails($identifier));

        return $user ? $user->getId() : 'SYSTEM';
    }
}
