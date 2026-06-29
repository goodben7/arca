<?php

namespace App\Manager;

use App\Entity\Employee;
use App\Entity\EmployeeSkill;
use App\Entity\User;
use App\Event\ActivityEvent;
use App\Event\Domain\EmployeeSkillLevelUpgradedEvent;
use App\Event\Domain\EmployeeSkillValidatedEvent;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Message\Query\GetUserDetails;
use App\Message\Query\QueryBusInterface;
use App\Model\NewEmployeeSkillModel;
use App\Model\SkillConstants;
use App\Model\ValidateEmployeeSkillModel;
use App\Repository\EmployeeSkillRepository;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EmployeeSkillManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private ActivityEventDispatcher $eventDispatcher,
        private Security $security,
        private QueryBusInterface $queries,
        private EventDispatcherInterface $domainEventDispatcher,
        private EmployeeSkillRepository $repository,
        private SkillManager $skills,
    ) {
    }

    public function assignFrom(NewEmployeeSkillModel $model): EmployeeSkill
    {
        $this->findEmployee($model->employee);
        $skill = $this->skills->find($model->skill);

        $existing = $this->repository->findOneByEmployeeAndSkill($model->employee, $skill->getId());
        if (null !== $existing) {
            throw new InvalidActionInputException('employee already has this skill assigned');
        }

        $employeeSkill = (new EmployeeSkill())
            ->setEmployee($model->employee)
            ->setSkill($skill)
            ->setLevel($model->level);

        $this->em->persist($employeeSkill);
        $this->em->flush();

        $this->eventDispatcher->dispatch($employeeSkill, ActivityEvent::ACTION_CREATE);

        return $employeeSkill;
    }

    public function validateFrom(ValidateEmployeeSkillModel $model): EmployeeSkill
    {
        $employeeSkill = $this->findEmployeeSkill($model->employeeSkillId);

        if (null !== $employeeSkill->getValidatedAt()) {
            throw new InvalidActionInputException('employee skill is already validated');
        }

        $employeeSkill->setValidatedAt(new \DateTimeImmutable());
        $this->em->flush();

        $this->eventDispatcher->dispatch($employeeSkill, ActivityEvent::ACTION_EDIT, null, 'employee skill validated');

        $this->domainEventDispatcher->dispatch(
            new EmployeeSkillValidatedEvent($employeeSkill, $this->resolveActorId())
        );

        return $employeeSkill;
    }

    public function applyUpdate(EmployeeSkill $employeeSkill): EmployeeSkill
    {
        $unitOfWork = $this->em->getUnitOfWork();
        $unitOfWork->computeChangeSets();
        $original = $unitOfWork->getOriginalEntityData($employeeSkill);

        $previousLevel = $original['level'] ?? $employeeSkill->getLevel();
        $previousValidatedAt = $original['validatedAt'] ?? null;

        $this->em->flush();

        $this->eventDispatcher->dispatch($employeeSkill, ActivityEvent::ACTION_EDIT);

        $currentLevel = $employeeSkill->getLevel();
        if (null !== $currentLevel
            && null !== $previousLevel
            && $currentLevel !== $previousLevel
            && SkillConstants::isLevelUpgrade($previousLevel, $currentLevel)
        ) {
            $this->domainEventDispatcher->dispatch(
                new EmployeeSkillLevelUpgradedEvent($employeeSkill, $previousLevel, $this->resolveActorId())
            );
        }

        if (null === $previousValidatedAt && null !== $employeeSkill->getValidatedAt()) {
            $this->domainEventDispatcher->dispatch(
                new EmployeeSkillValidatedEvent($employeeSkill, $this->resolveActorId())
            );
        }

        return $employeeSkill;
    }

    private function findEmployeeSkill(?string $employeeSkillId): EmployeeSkill
    {
        if (!$employeeSkillId) {
            throw new InvalidActionInputException('employeeSkillId is required');
        }

        $employeeSkill = $this->em->find(EmployeeSkill::class, $employeeSkillId);

        if (null === $employeeSkill) {
            throw new UnavailableDataException(sprintf('cannot find employee skill with id: %s', $employeeSkillId));
        }

        return $employeeSkill;
    }

    private function findEmployee(?string $employeeId): Employee
    {
        if (!$employeeId) {
            throw new InvalidActionInputException('employee is required');
        }

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
