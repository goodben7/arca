<?php

namespace App\Manager;

use App\Entity\Employee;
use App\Entity\MobilityRequest;
use App\Entity\User;
use App\Event\ActivityEvent;
use App\Event\Domain\MobilityImplementedEvent;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Message\Query\GetUserDetails;
use App\Message\Query\QueryBusInterface;
use App\Model\EligibilityActionConstants;
use App\Model\MobilityRequestConstants;
use App\Model\NewMobilityRequestModel;
use App\Model\SubmitMobilityRequestModel;
use App\Policy\PolicyEvaluator;
use App\Service\ActivityEventDispatcher;
use App\Workflow\MobilityApprovalWorkflow;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class MobilityRequestManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private ActivityEventDispatcher $eventDispatcher,
        private Security $security,
        private QueryBusInterface $queries,
        private EventDispatcherInterface $domainEventDispatcher,
        private MobilityApprovalWorkflow $approvalWorkflow,
        private JobRoleManager $jobRoles,
        private GradeManager $grades,
        private PolicyEvaluator $policyEvaluator,
    ) {
    }

    public function createFrom(NewMobilityRequestModel $model): MobilityRequest
    {
        $employee = $this->findEmployee($model->employee);
        $this->assertTargetsForType($model->type, $model->targetJobRoleId, $model->targetDepartment);

        $request = (new MobilityRequest())
            ->setEmployee($employee->getId())
            ->setType($model->type)
            ->setStatus(MobilityRequestConstants::STATUS_DRAFT)
            ->setTargetDepartment($model->targetDepartment)
            ->setReason($model->reason);

        if (null !== $model->targetJobRoleId) {
            $request->setTargetJobRole($this->jobRoles->find($model->targetJobRoleId));
        }

        if (null !== $model->targetGradeId) {
            $request->setTargetGrade($this->grades->find($model->targetGradeId));
        }

        $this->em->persist($request);
        $this->em->flush();

        $this->eventDispatcher->dispatch($request, ActivityEvent::ACTION_CREATE);

        return $request;
    }

    public function submitFrom(SubmitMobilityRequestModel $model): MobilityRequest
    {
        $request = $this->findMobilityRequest($model->mobilityRequestId);

        if (MobilityRequestConstants::TYPE_PROMOTION === $request->getType()) {
            $employee = $this->findEmployee((string) $request->getEmployee());
            $targetJobRole = $request->getTargetJobRole();
            if (null === $targetJobRole || null === $targetJobRole->getId()) {
                throw new InvalidActionInputException('target job role is required for promotion requests');
            }

            $result = $this->policyEvaluator->evaluate(
                EligibilityActionConstants::PROMOTION,
                $employee,
                ['targetJobRoleId' => $targetJobRole->getId()],
            );

            if (!$result->isEligible()) {
                throw new InvalidActionInputException(implode('; ', $result->getReasons()));
            }
        }

        $this->applyWorkflowAction($request, MobilityApprovalWorkflow::ACTION_SUBMIT);
        $this->em->flush();

        $this->eventDispatcher->dispatch($request, ActivityEvent::ACTION_EDIT, null, 'mobility request submitted');

        return $request;
    }

    public function approve(string $mobilityRequestId): MobilityRequest
    {
        $request = $this->findMobilityRequest($mobilityRequestId);

        $this->applyWorkflowAction($request, MobilityApprovalWorkflow::ACTION_APPROVE);
        $this->em->flush();

        $this->eventDispatcher->dispatch($request, ActivityEvent::ACTION_EDIT, null, 'mobility request approved');

        if (MobilityRequestConstants::STATUS_IMPLEMENTED === $request->getStatus()) {
            $this->domainEventDispatcher->dispatch(
                new MobilityImplementedEvent($request, $this->resolveActorId())
            );
        }

        return $request;
    }

    public function reject(string $mobilityRequestId, string $reason): MobilityRequest
    {
        $request = $this->findMobilityRequest($mobilityRequestId);
        $this->applyWorkflowAction($request, MobilityApprovalWorkflow::ACTION_REJECT, $reason);
        $this->em->flush();

        $this->eventDispatcher->dispatch($request, ActivityEvent::ACTION_EDIT, null, 'mobility request rejected');

        return $request;
    }

    public function cancel(string $mobilityRequestId): MobilityRequest
    {
        $request = $this->findMobilityRequest($mobilityRequestId);
        $this->applyWorkflowAction($request, MobilityApprovalWorkflow::ACTION_CANCEL);
        $this->em->flush();

        $this->eventDispatcher->dispatch($request, ActivityEvent::ACTION_EDIT, null, 'mobility request cancelled');

        return $request;
    }

    private function applyWorkflowAction(MobilityRequest $request, string $action, ?string $reason = null): void
    {
        if (!$this->approvalWorkflow->supports($request)) {
            throw new InvalidActionInputException('mobility request approval workflow does not support this subject');
        }

        if (!\in_array($action, $this->approvalWorkflow->getAvailableActions($request), true)) {
            throw new InvalidActionInputException('Action not allowed : invalid mobility request state');
        }

        $context = ['actorId' => $this->resolveActorId()];
        if (null !== $reason) {
            $context['reason'] = $reason;
        }

        $this->approvalWorkflow->apply($request, $action, $context);
    }

    private function assertTargetsForType(string $type, ?string $targetJobRoleId, ?string $targetDepartment): void
    {
        if (\in_array($type, [MobilityRequestConstants::TYPE_PROMOTION, MobilityRequestConstants::TYPE_DEMOTION], true)
            && null === $targetJobRoleId) {
            throw new InvalidActionInputException('targetJobRoleId is required for promotion and demotion requests');
        }

        if (MobilityRequestConstants::TYPE_TRANSFER === $type
            && null === $targetDepartment
            && null === $targetJobRoleId) {
            throw new InvalidActionInputException('targetDepartment or targetJobRoleId is required for transfer requests');
        }
    }

    private function findMobilityRequest(?string $mobilityRequestId): MobilityRequest
    {
        if (!$mobilityRequestId) {
            throw new InvalidActionInputException('mobilityRequestId is required');
        }

        $request = $this->em->find(MobilityRequest::class, $mobilityRequestId);

        if (null === $request) {
            throw new UnavailableDataException(sprintf('cannot find mobility request with id: %s', $mobilityRequestId));
        }

        return $request;
    }

    private function findEmployee(string $employeeId): Employee
    {
        $employee = $this->em->find(Employee::class, $employeeId);

        if (null === $employee) {
            throw new UnavailableDataException(sprintf('cannot find employee with id: %s', $employeeId));
        }

        return $employee;
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
