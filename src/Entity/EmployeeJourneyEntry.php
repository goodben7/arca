<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use App\Doctrine\IdGenerator;
use App\Model\JourneyEventTypeConstants;
use App\Model\JourneyStageConstants;
use App\Model\RessourceInterface;
use App\Provider\EmployeeJourneyProvider;
use App\Repository\EmployeeJourneyEntryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EmployeeJourneyEntryRepository::class)]
#[ORM\Table(name: '`employee_journey_entry`')]
#[ORM\Index(name: 'IDX_EJ_EMPLOYEE_OCCURRED_AT', columns: ['EJ_EMPLOYEE', 'EJ_OCCURRED_AT'])]
#[ApiResource(
    normalizationContext: ['groups' => 'employee_journey:get'],
    operations: [
        new GetCollection(
            uriTemplate: '/employees/{employeeId}/journey',
            uriVariables: [
                'employeeId' => new Link(fromClass: Employee::class),
            ],
            security: 'is_granted("ROLE_EMPLOYEE_JOURNEY_LIST")',
            provider: EmployeeJourneyProvider::class,
        ),
    ],
)]
#[ApiFilter(\ApiPlatform\Doctrine\Orm\Filter\OrderFilter::class, properties: ['occurredAt'])]
class EmployeeJourneyEntry implements RessourceInterface
{
    public const string ID_PREFIX = 'EJ';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'EJ_ID', length: 16)]
    #[Groups(['employee_journey:get'])]
    private ?string $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'EJ_EMPLOYEE', referencedColumnName: 'EM_ID', nullable: false)]
    #[Groups(['employee_journey:get'])]
    private ?Employee $employee = null;

    #[ORM\Column(name: 'EJ_STAGE', length: 30)]
    #[Assert\Choice(callback: [JourneyStageConstants::class, 'getStages'])]
    #[Groups(['employee_journey:get'])]
    private ?string $stage = null;

    #[ORM\Column(name: 'EJ_EVENT_TYPE', length: 40)]
    #[Assert\Choice(callback: [JourneyEventTypeConstants::class, 'getEventTypes'])]
    #[Groups(['employee_journey:get'])]
    private ?string $eventType = null;

    #[ORM\Column(name: 'EJ_SOURCE_ENTITY_TYPE', length: 40, nullable: true)]
    #[Groups(['employee_journey:get'])]
    private ?string $sourceEntityType = null;

    #[ORM\Column(name: 'EJ_SOURCE_ENTITY_ID', length: 16, nullable: true)]
    #[Groups(['employee_journey:get'])]
    private ?string $sourceEntityId = null;

    #[ORM\Column(name: 'EJ_METADATA', type: Types::JSON, nullable: true)]
    #[Groups(['employee_journey:get'])]
    private ?array $metadata = null;

    #[ORM\Column(name: 'EJ_OCCURRED_AT')]
    #[Groups(['employee_journey:get'])]
    private ?\DateTimeImmutable $occurredAt = null;

    #[ORM\Column(name: 'EJ_ACTOR_ID', length: 16, nullable: true)]
    #[Groups(['employee_journey:get'])]
    private ?string $actorId = null;

    #[ORM\Column(name: 'EJ_DESCRIPTION', length: 255, nullable: true)]
    #[Groups(['employee_journey:get'])]
    private ?string $description = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getEmployee(): ?Employee
    {
        return $this->employee;
    }

    public function setEmployee(Employee $employee): static
    {
        $this->employee = $employee;

        return $this;
    }

    #[Groups(['employee_journey:get'])]
    public function getEmployeeId(): ?string
    {
        return $this->employee?->getId();
    }

    public function getStage(): ?string
    {
        return $this->stage;
    }

    public function setStage(string $stage): static
    {
        $this->stage = $stage;

        return $this;
    }

    public function getEventType(): ?string
    {
        return $this->eventType;
    }

    public function setEventType(string $eventType): static
    {
        $this->eventType = $eventType;

        return $this;
    }

    public function getSourceEntityType(): ?string
    {
        return $this->sourceEntityType;
    }

    public function setSourceEntityType(?string $sourceEntityType): static
    {
        $this->sourceEntityType = $sourceEntityType;

        return $this;
    }

    public function getSourceEntityId(): ?string
    {
        return $this->sourceEntityId;
    }

    public function setSourceEntityId(?string $sourceEntityId): static
    {
        $this->sourceEntityId = $sourceEntityId;

        return $this;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): static
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function getOccurredAt(): ?\DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function setOccurredAt(\DateTimeImmutable $occurredAt): static
    {
        $this->occurredAt = $occurredAt;

        return $this;
    }

    public function getActorId(): ?string
    {
        return $this->actorId;
    }

    public function setActorId(?string $actorId): static
    {
        $this->actorId = $actorId;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }
}
