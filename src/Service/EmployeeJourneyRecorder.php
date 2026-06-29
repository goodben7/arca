<?php

namespace App\Service;

use App\Entity\Employee;
use App\Entity\EmployeeJourneyEntry;
use App\Entity\User;
use App\Exception\UnavailableDataException;
use App\Message\Query\GetUserDetails;
use App\Message\Query\QueryBusInterface;
use App\Model\JourneyEventTypeConstants;
use App\Model\JourneyStageConstants;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class EmployeeJourneyRecorder
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security,
        private QueryBusInterface $queries,
    ) {
    }

    public function record(
        Employee|string $employee,
        string $stage,
        string $eventType,
        ?string $sourceEntityType = null,
        ?string $sourceEntityId = null,
        ?array $metadata = null,
        ?string $description = null,
        ?string $actorId = null,
        ?\DateTimeImmutable $occurredAt = null,
    ): EmployeeJourneyEntry {
        if (!in_array($stage, JourneyStageConstants::getStages(), true)) {
            throw new \InvalidArgumentException(sprintf('Invalid journey stage: %s', $stage));
        }

        if (!in_array($eventType, JourneyEventTypeConstants::getEventTypes(), true)) {
            throw new \InvalidArgumentException(sprintf('Invalid journey event type: %s', $eventType));
        }

        $employeeEntity = $this->resolveEmployee($employee);

        $entry = (new EmployeeJourneyEntry())
            ->setEmployee($employeeEntity)
            ->setStage($stage)
            ->setEventType($eventType)
            ->setSourceEntityType($sourceEntityType)
            ->setSourceEntityId($sourceEntityId)
            ->setMetadata($metadata)
            ->setDescription($description)
            ->setActorId($actorId ?? $this->resolveActorId())
            ->setOccurredAt($occurredAt ?? new \DateTimeImmutable());

        $this->em->persist($entry);
        $this->em->flush();

        return $entry;
    }

    private function resolveEmployee(Employee|string $employee): Employee
    {
        if ($employee instanceof Employee) {
            if (null === $employee->getId()) {
                throw new UnavailableDataException('cannot record journey for employee without id');
            }

            return $employee;
        }

        $employeeEntity = $this->em->find(Employee::class, $employee);

        if (null === $employeeEntity) {
            throw new UnavailableDataException(sprintf('cannot find employee with id: %s', $employee));
        }

        return $employeeEntity;
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
