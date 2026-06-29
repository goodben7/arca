<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Doctrine\IdGenerator;
use App\Dto\ActivateObjectiveDto;
use App\Dto\CancelObjectiveDto;
use App\Dto\CompleteObjectiveDto;
use App\Dto\CreateObjectiveDto;
use App\Model\ObjectiveConstants;
use App\Model\RessourceInterface;
use App\Repository\ObjectiveRepository;
use App\State\ActivateObjectiveProcessor;
use App\State\CancelObjectiveProcessor;
use App\State\CompleteObjectiveProcessor;
use App\State\CreateObjectiveProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ObjectiveRepository::class)]
#[ORM\Table(name: '`objective`')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'objective:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_OBJECTIVE_DETAILS")',
            provider: ItemProvider::class,
        ),
        new GetCollection(
            security: 'is_granted("ROLE_OBJECTIVE_LIST")',
            provider: CollectionProvider::class,
        ),
        new Post(
            security: 'is_granted("ROLE_OBJECTIVE_CREATE")',
            input: CreateObjectiveDto::class,
            processor: CreateObjectiveProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_OBJECTIVE_UPDATE")',
            denormalizationContext: ['groups' => 'objective:patch'],
            processor: PersistProcessor::class,
        ),
        new Post(
            uriTemplate: '/objectives/activations',
            security: 'is_granted("ROLE_OBJECTIVE_ACTIVATE")',
            input: ActivateObjectiveDto::class,
            processor: ActivateObjectiveProcessor::class,
            status: 200,
        ),
        new Post(
            uriTemplate: '/objectives/completions',
            security: 'is_granted("ROLE_OBJECTIVE_COMPLETE")',
            input: CompleteObjectiveDto::class,
            processor: CompleteObjectiveProcessor::class,
            status: 200,
        ),
        new Post(
            uriTemplate: '/objectives/cancellations',
            security: 'is_granted("ROLE_OBJECTIVE_CANCEL")',
            input: CancelObjectiveDto::class,
            processor: CancelObjectiveProcessor::class,
            status: 200,
        ),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'employee' => 'exact',
    'cycle' => 'exact',
    'status' => 'exact',
    'title' => 'partial',
])]
#[ApiFilter(OrderFilter::class, properties: ['dueDate', 'createdAt'])]
#[ApiFilter(DateFilter::class, properties: ['dueDate', 'createdAt'])]
class Objective implements RessourceInterface
{
    public const string ID_PREFIX = 'OB';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'OB_ID', length: 16)]
    #[Groups(['objective:get'])]
    private ?string $id = null;

    #[ORM\Column(name: 'OB_EMPLOYEE', length: 16)]
    #[Groups(['objective:get'])]
    #[Assert\NotBlank]
    private ?string $employee = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'OB_CYCLE', referencedColumnName: 'EC_ID', nullable: false)]
    #[Groups(['objective:get'])]
    #[Assert\NotNull]
    private ?EvaluationCycle $cycle = null;

    #[ORM\Column(name: 'OB_TITLE', length: 160)]
    #[Groups(['objective:get', 'objective:patch'])]
    #[Assert\NotBlank]
    private ?string $title = null;

    #[ORM\Column(name: 'OB_DESCRIPTION', type: Types::TEXT, nullable: true)]
    #[Groups(['objective:get', 'objective:patch'])]
    private ?string $description = null;

    #[ORM\Column(name: 'OB_SPECIFIC', type: Types::TEXT)]
    #[Groups(['objective:get', 'objective:patch'])]
    #[Assert\NotBlank]
    private ?string $specific = null;

    #[ORM\Column(name: 'OB_MEASURABLE', type: Types::TEXT)]
    #[Groups(['objective:get', 'objective:patch'])]
    #[Assert\NotBlank]
    private ?string $measurable = null;

    #[ORM\Column(name: 'OB_TARGET_VALUE', length: 120, nullable: true)]
    #[Groups(['objective:get', 'objective:patch'])]
    private ?string $targetValue = null;

    #[ORM\Column(name: 'OB_ACHIEVABLE', type: Types::TEXT, nullable: true)]
    #[Groups(['objective:get', 'objective:patch'])]
    private ?string $achievable = null;

    #[ORM\Column(name: 'OB_RELEVANT', type: Types::TEXT, nullable: true)]
    #[Groups(['objective:get', 'objective:patch'])]
    private ?string $relevant = null;

    #[ORM\Column(name: 'OB_DUE_DATE', type: Types::DATE_IMMUTABLE)]
    #[Groups(['objective:get', 'objective:patch'])]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $dueDate = null;

    #[ORM\Column(name: 'OB_STATUS', length: 15)]
    #[Groups(['objective:get'])]
    #[Assert\Choice(callback: [ObjectiveConstants::class, 'getStatuses'])]
    private ?string $status = null;

    #[ORM\Column(name: 'OB_CREATED_AT')]
    #[Groups(['objective:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'OB_UPDATED_AT', nullable: true)]
    #[Groups(['objective:get'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getEmployee(): ?string
    {
        return $this->employee;
    }

    public function setEmployee(string $employee): static
    {
        $this->employee = $employee;

        return $this;
    }

    public function getCycle(): ?EvaluationCycle
    {
        return $this->cycle;
    }

    public function setCycle(EvaluationCycle $cycle): static
    {
        $this->cycle = $cycle;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

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

    public function getSpecific(): ?string
    {
        return $this->specific;
    }

    public function setSpecific(string $specific): static
    {
        $this->specific = $specific;

        return $this;
    }

    public function getMeasurable(): ?string
    {
        return $this->measurable;
    }

    public function setMeasurable(string $measurable): static
    {
        $this->measurable = $measurable;

        return $this;
    }

    public function getTargetValue(): ?string
    {
        return $this->targetValue;
    }

    public function setTargetValue(?string $targetValue): static
    {
        $this->targetValue = $targetValue;

        return $this;
    }

    public function getAchievable(): ?string
    {
        return $this->achievable;
    }

    public function setAchievable(?string $achievable): static
    {
        $this->achievable = $achievable;

        return $this;
    }

    public function getRelevant(): ?string
    {
        return $this->relevant;
    }

    public function setRelevant(?string $relevant): static
    {
        $this->relevant = $relevant;

        return $this;
    }

    public function getDueDate(): ?\DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTimeImmutable $dueDate): static
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    #[ORM\PreUpdate]
    public function updateUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    public function buildCreatedAt(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }
}
