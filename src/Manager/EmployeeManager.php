<?php

namespace App\Manager;

use App\Entity\Employee;
use App\Entity\User;
use App\Enum\EntityType;
use App\Event\ActivityEvent;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Message\Command\CommandBusInterface;
use App\Message\Command\CreateUserCommand;
use App\Message\Query\GetUserDetails;
use App\Message\Query\QueryBusInterface;
use App\Model\ActivateEmployeeModel;
use App\Model\AssignManagerEmployeeModel;
use App\Model\DeactivateEmployeeModel;
use App\Model\EmployeeConstants;
use App\Model\NewEmployeeModel;
use App\Model\PutEmployeeOnLeaveModel;
use App\Model\PutEmployeeOnProbationModel;
use App\Model\RetireEmployeeModel;
use App\Model\SuspendEmployeeModel;
use App\Model\TerminateEmployeeModel;
use App\Model\UserProxyIntertace;
use App\Repository\ProfileRepository;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class EmployeeManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private ActivityEventDispatcher $eventDispatcher,
        private Security $security,
        private ProfileRepository $profileRepository,
        private QueryBusInterface $queries,
        private CommandBusInterface $bus,
    ) {
    }

    public function generateEmployeeNumber(): string
    {
        $date = (new \DateTime())->format('Ymd');
        $rand = substr(strtoupper(bin2hex(random_bytes(3))), 0, 6);
        return "EMP{$date}{$rand}";
    }

    public function buildFromData(Employee $employee, array $data): Employee
    {
        if (!$employee->getEmployeeNumber()) {
            $employee->setEmployeeNumber($this->generateEmployeeNumber());
        }
        return $employee;
    }

    public function createFrom(NewEmployeeModel $model): Employee
    {
        $userId = $this->security->getUser()?->getUserIdentifier();

        /** @var User|null $user */
        $user = null;
        if ($userId) {
            $user = $this->queries->ask(new GetUserDetails($userId));
        }

        $employee = new Employee();

        $employee
            ->setCreatedBy($user ? $user->getId() : 'SYSTEM') 
            ->setFirstName($model->firstName)
            ->setLastName($model->lastName) 
            ->setEmail($model->email)
            ->setPhone($model->phone)
            ->setGender($model->gender)
            ->setBirthDate($model->birthDate)
            ->setNationality($model->nationality)
            ->setMaritalStatus($model->maritalStatus)
            ->setHireDate($model->hireDate)
            ->setDepartureDate($model->departureDate)
            ->setStatus(EmployeeConstants::STATUS_ACTIVE)
            ->setDepartment($model->department)
            ->setPosition($model->position)
            ->setCreatedAt(new \DateTimeImmutable('now'));

        if ($model->employeeNumber) {
            $employee->setEmployeeNumber($model->employeeNumber);
        } else {
            $employee->setEmployeeNumber($this->generateEmployeeNumber());
        }


        $this->em->persist($employee);

        $profile = $model->profile ?: $this->profileRepository->findOneBy(['personType' => UserProxyIntertace::PERSON_EMPLOYEE]);

        if (null === $profile) {
            throw new UnavailableDataException('cannot find profile with person type: employee');
        }
        
        $user = $this->bus->dispatch(
            new CreateUserCommand(
            $employee->getEmail(),
            $employee->getEmail(),
            $profile,
            $employee->getPhone(),
            $employee->getDisplayName(),
            $employee->getId(),
            EntityType::EMPLOYEE
            )
        );

        $employee->setUserId($user->getId());  

        $this->em->persist($employee);

        $this->em->flush();

        $this->eventDispatcher->dispatch($employee, ActivityEvent::ACTION_CREATE);

        return $employee;
    }

    public function activateFrom(ActivateEmployeeModel $model): Employee
    {
        $employee = $this->findEmployee($model->employeeId);

        $this->assertActionAllowed($employee, EmployeeConstants::ACTION_ACTIVATE);
        $this->applyEmployeeAction($employee, EmployeeConstants::ACTION_ACTIVATE);
        $this->em->flush();

        $this->eventDispatcher->dispatch($employee, ActivityEvent::ACTION_EDIT, null, 'employee activated');

        return $employee;
    }

    public function deactivateFrom(DeactivateEmployeeModel $model): Employee
    {
        $employee = $this->findEmployee($model->employeeId);

        $this->assertActionAllowed($employee, EmployeeConstants::ACTION_DEACTIVATE);
        $this->applyEmployeeAction($employee, EmployeeConstants::ACTION_DEACTIVATE);
        $this->em->flush();

        $this->eventDispatcher->dispatch($employee, ActivityEvent::ACTION_EDIT, null, 'employee deactivated');

        return $employee;
    }

    public function putOnLeaveFrom(PutEmployeeOnLeaveModel $model): Employee
    {
        $employee = $this->findEmployee($model->employeeId);

        $this->assertActionAllowed($employee, EmployeeConstants::ACTION_SET_ON_LEAVE);
        $this->applyEmployeeAction($employee, EmployeeConstants::ACTION_SET_ON_LEAVE);
        $this->em->flush();

        $this->eventDispatcher->dispatch($employee, ActivityEvent::ACTION_EDIT, null, 'employee put on leave');

        return $employee;
    }

    public function putOnProbationFrom(PutEmployeeOnProbationModel $model): Employee
    {
        $employee = $this->findEmployee($model->employeeId);

        $this->assertActionAllowed($employee, EmployeeConstants::ACTION_SET_PROBATION);
        $this->applyEmployeeAction($employee, EmployeeConstants::ACTION_SET_PROBATION);
        $this->em->flush();

        $this->eventDispatcher->dispatch($employee, ActivityEvent::ACTION_EDIT, null, 'employee put on probation');

        return $employee;
    }

    public function suspendFrom(SuspendEmployeeModel $model): Employee
    {
        $employee = $this->findEmployee($model->employeeId);

        $this->assertActionAllowed($employee, EmployeeConstants::ACTION_SUSPEND);
        $this->applyEmployeeAction($employee, EmployeeConstants::ACTION_SUSPEND);
        $this->em->flush();

        $this->eventDispatcher->dispatch($employee, ActivityEvent::ACTION_EDIT, null, 'employee suspended');

        return $employee;
    }

    public function terminateFrom(TerminateEmployeeModel $model): Employee
    {
        $employee = $this->findEmployee($model->employeeId);

        $this->assertActionAllowed($employee, EmployeeConstants::ACTION_TERMINATE);
        $this->applyEmployeeAction($employee, EmployeeConstants::ACTION_TERMINATE);
        $this->em->flush();

        $this->eventDispatcher->dispatch($employee, ActivityEvent::ACTION_EDIT, null, 'employee terminated');

        return $employee;
    }

    public function retireFrom(RetireEmployeeModel $model): Employee
    {
        $employee = $this->findEmployee($model->employeeId);

        $this->assertActionAllowed($employee, EmployeeConstants::ACTION_RETIRE);
        $this->applyEmployeeAction($employee, EmployeeConstants::ACTION_RETIRE);
        $this->em->flush();

        $this->eventDispatcher->dispatch($employee, ActivityEvent::ACTION_EDIT, null, 'employee retired');

        return $employee;
    }

    public function assignManagerFrom(AssignManagerEmployeeModel $model): Employee
    {
        $employee = $this->findEmployee($model->employeeId);
        $manager = $this->findEmployee($model->managerId);

        if ($employee->getId() === $manager->getId()) {
            throw new InvalidActionInputException('Action not allowed : manager cannot be the same employee');
        }

        //$this->assertActionAllowed($employee, EmployeeConstants::ACTION_ASSIGN_MANAGER);
        $this->applyManagerAssignment($employee, $manager->getId());
        $this->em->flush();

        $this->eventDispatcher->dispatch($employee, ActivityEvent::ACTION_EDIT, null, 'employee manager assigned');

        return $employee;
    }

    private function assertActionAllowed(Employee $employee, string $action): void
    {
        $allowed = EmployeeConstants::getAllowedActionsForStatus($employee->getStatus());
        if (!in_array($action, $allowed, true)) {
            throw new InvalidActionInputException('Action not allowed : invalid employee state');
        }
    }

    private function applyEmployeeAction(Employee $employee, string $action): void
    {
        $now = new \DateTimeImmutable();
        $actorId = $this->resolveActorId();

        match ($action) {
            EmployeeConstants::ACTION_ACTIVATE => $employee
                ->setStatus(EmployeeConstants::STATUS_ACTIVE)
                ->setActivatedAt($now)
                ->setActivatedBy($actorId),
            EmployeeConstants::ACTION_DEACTIVATE => $employee
                ->setStatus(EmployeeConstants::STATUS_INACTIVE)
                ->setDeactivatedAt($now)
                ->setDeactivatedBy($actorId),
            EmployeeConstants::ACTION_SET_ON_LEAVE => $employee
                ->setStatus(EmployeeConstants::STATUS_ON_LEAVE)
                ->setOnLeaveAt($now)
                ->setOnLeaveBy($actorId),
            EmployeeConstants::ACTION_SUSPEND => $employee
                ->setStatus(EmployeeConstants::STATUS_SUSPENDED)
                ->setSuspendedAt($now)
                ->setSuspendedBy($actorId),
            EmployeeConstants::ACTION_TERMINATE => $employee
                ->setStatus(EmployeeConstants::STATUS_TERMINATED)
                ->setTerminatedAt($now)
                ->setTerminatedBy($actorId)
                ->setDepartureDate($employee->getDepartureDate() ?: $now),
            EmployeeConstants::ACTION_RETIRE => $employee
                ->setStatus(EmployeeConstants::STATUS_RETIRED)
                ->setRetiredAt($now)
                ->setRetiredBy($actorId)
                ->setDepartureDate($employee->getDepartureDate() ?: $now),
            EmployeeConstants::ACTION_SET_PROBATION => $employee
                ->setStatus(EmployeeConstants::STATUS_PROBATION)
                ->setProbationAt($now)
                ->setProbationBy($actorId),
            default => throw new InvalidActionInputException('Action not allowed : unknown action'),
        };
    }

    private function applyManagerAssignment(Employee $employee, string $managerEmployeeId): void
    {
        $now = new \DateTimeImmutable();
        $actorId = $this->resolveActorId();

        $employee
            ->setManager($managerEmployeeId)
            ->setManagerAssignedAt($now)
            ->setManagerAssignedBy($actorId)
        ;
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

    private function findEmployee(?string $employeeId): Employee
    {
        if (!$employeeId) {
            throw new InvalidActionInputException('employeeId is required');
        }

        $employee = $this->em->find(Employee::class, $employeeId);

        if (null === $employee) {
            throw new UnavailableDataException(sprintf('cannot find employee with id: %s', $employeeId));
        }

        return $employee;
    }
}
