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
use App\Dto\CloseEvaluationCycleDto;
use App\Dto\CreateEvaluationCycleDto;
use App\Dto\OpenEvaluationCycleDto;
use App\Model\EvaluationCycleConstants;
use App\Model\RessourceInterface;
use App\Repository\EvaluationCycleRepository;
use App\State\CloseEvaluationCycleProcessor;
use App\State\CreateEvaluationCycleProcessor;
use App\State\OpenEvaluationCycleProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EvaluationCycleRepository::class)]
#[ORM\Table(name: '`evaluation_cycle`')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'evaluation_cycle:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_EVALUATION_CYCLE_DETAILS")',
            provider: ItemProvider::class,
        ),
        new GetCollection(
            security: 'is_granted("ROLE_EVALUATION_CYCLE_LIST")',
            provider: CollectionProvider::class,
        ),
        new Post(
            security: 'is_granted("ROLE_EVALUATION_CYCLE_CREATE")',
            input: CreateEvaluationCycleDto::class,
            processor: CreateEvaluationCycleProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_EVALUATION_CYCLE_UPDATE")',
            denormalizationContext: ['groups' => 'evaluation_cycle:patch'],
            processor: PersistProcessor::class,
        ),
        new Post(
            uriTemplate: '/evaluation_cycles/opens',
            security: 'is_granted("ROLE_EVALUATION_CYCLE_OPEN")',
            input: OpenEvaluationCycleDto::class,
            processor: OpenEvaluationCycleProcessor::class,
            status: 200,
        ),
        new Post(
            uriTemplate: '/evaluation_cycles/closures',
            security: 'is_granted("ROLE_EVALUATION_CYCLE_CLOSE")',
            input: CloseEvaluationCycleDto::class,
            processor: CloseEvaluationCycleProcessor::class,
            status: 200,
        ),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'name' => 'partial',
    'year' => 'exact',
    'status' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['year', 'startDate', 'createdAt'])]
#[ApiFilter(DateFilter::class, properties: ['startDate', 'endDate', 'createdAt'])]
class EvaluationCycle implements RessourceInterface
{
    public const string ID_PREFIX = 'EC';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'EC_ID', length: 16)]
    #[Groups(['evaluation_cycle:get', 'performance_review:get', 'objective:get'])]
    private ?string $id = null;

    #[ORM\Column(name: 'EC_NAME', length: 120)]
    #[Groups(['evaluation_cycle:get', 'evaluation_cycle:patch'])]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(name: 'EC_YEAR', type: Types::SMALLINT)]
    #[Groups(['evaluation_cycle:get', 'evaluation_cycle:patch'])]
    #[Assert\NotNull]
    private ?int $year = null;

    #[ORM\Column(name: 'EC_STATUS', length: 15)]
    #[Groups(['evaluation_cycle:get'])]
    #[Assert\Choice(callback: [EvaluationCycleConstants::class, 'getStatuses'])]
    private ?string $status = null;

    #[ORM\Column(name: 'EC_START_DATE', type: Types::DATE_IMMUTABLE)]
    #[Groups(['evaluation_cycle:get', 'evaluation_cycle:patch'])]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(name: 'EC_END_DATE', type: Types::DATE_IMMUTABLE)]
    #[Groups(['evaluation_cycle:get', 'evaluation_cycle:patch'])]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\Column(name: 'EC_OPENED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['evaluation_cycle:get'])]
    private ?\DateTimeImmutable $openedAt = null;

    #[ORM\Column(name: 'EC_CLOSED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['evaluation_cycle:get'])]
    private ?\DateTimeImmutable $closedAt = null;

    #[ORM\Column(name: 'EC_CREATED_AT')]
    #[Groups(['evaluation_cycle:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'EC_UPDATED_AT', nullable: true)]
    #[Groups(['evaluation_cycle:get'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): static
    {
        $this->year = $year;

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

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeImmutable $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getOpenedAt(): ?\DateTimeImmutable
    {
        return $this->openedAt;
    }

    public function setOpenedAt(?\DateTimeImmutable $openedAt): static
    {
        $this->openedAt = $openedAt;

        return $this;
    }

    public function getClosedAt(): ?\DateTimeImmutable
    {
        return $this->closedAt;
    }

    public function setClosedAt(?\DateTimeImmutable $closedAt): static
    {
        $this->closedAt = $closedAt;

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
