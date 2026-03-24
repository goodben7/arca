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
use App\Dto\ClosePositionDto;
use App\Dto\OpenPositionDto;
use App\Model\PositionLevel;
use App\Model\PositionStatusConstants;
use App\Model\RessourceInterface;
use App\Repository\PositionRepository;
use App\State\ClosePositionProcessor;
use App\State\OpenPositionProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PositionRepository::class)]
#[ORM\Table(name: '`position`')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'position:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_POSITION_DETAILS")',
            provider: ItemProvider::class
        ),
        new GetCollection(
            security: 'is_granted("ROLE_POSITION_LIST")',
            provider: CollectionProvider::class
        ),
        new Post(
            security: 'is_granted("ROLE_POSITION_CREATE")',
            denormalizationContext: ['groups' => 'position:post',],
            processor: PersistProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_POSITION_UPDATE")',
            denormalizationContext: ['groups' => 'position:patch',],
            processor: PersistProcessor::class,
        ),
        new Post(
            uriTemplate: '/positions/open',
            security: 'is_granted("ROLE_POSITION_OPEN")',
            input: OpenPositionDto::class,
            processor: OpenPositionProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/positions/close',
            security: 'is_granted("ROLE_POSITION_CLOSE")',
            input: ClosePositionDto::class,
            processor: ClosePositionProcessor::class,
            status: 200
        ),
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'title' => 'ipartial',
    'department' => 'ipartial',
    'level' => 'exact',
    'status' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'updatedAt'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'updatedAt'])]
class Position implements RessourceInterface
{
    public const string ID_PREFIX = 'PO';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'PO_ID', length: 16)]
    #[Groups(['position:get'])]
    private ?string $id = null;

    #[ORM\Column(name: 'PO_TITLE', length: 120)]
    #[Groups(['position:get', 'position:post', 'position:patch'])]
    #[Assert\NotBlank]
    private ?string $title = null;

    #[ORM\Column(name: 'PO_DEPARTMENT', length: 120)]
    #[Groups(['position:get', 'position:post', 'position:patch'])]
    #[Assert\NotBlank]
    private ?string $department = null;

    #[ORM\Column(name: 'PO_LEVEL', length: 15)]
    #[Groups(['position:get', 'position:post', 'position:patch'])]
    #[Assert\Choice(callback: [PositionLevel::class, 'getLevels'])]
    #[Assert\NotBlank]
    private ?string $level = null;

    #[ORM\Column(name: 'PO_DESCRIPTION', type: Types::TEXT, nullable: true)]
    #[Groups(['position:get', 'position:post', 'position:patch'])]
    private ?string $description = null;

    #[ORM\Column(name: 'PO_HEADCOUNT')]
    #[Groups(['position:get', 'position:post', 'position:patch'])]
    #[Assert\PositiveOrZero]
    #[Assert\NotNull]
    private ?int $headcount = null;

    #[ORM\Column(name: 'PO_OPEN_POSITIONS')]
    #[Groups(['position:get', 'position:post', 'position:patch'])]
    #[Assert\PositiveOrZero]
    #[Assert\NotNull]
    private ?int $openPositions = null;

    #[ORM\Column(name: 'PO_STATUS', length: 15)]
    #[Groups(['position:get', 'position:post', 'position:patch'])]
    #[Assert\Choice(callback: [PositionStatusConstants::class, 'getStatuses'])]
    #[Assert\NotBlank]
    private ?string $status = null;

    #[ORM\Column(name: 'PO_OPENED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['position:get'])]
    private ?\DateTimeImmutable $openedAt = null;

    #[ORM\Column(name: 'PO_OPENED_BY', length: 16, nullable: true)]
    #[Groups(['position:get'])]
    private ?string $openedBy = null;

    #[ORM\Column(name: 'PO_CLOSED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['position:get'])]
    private ?\DateTimeImmutable $closedAt = null;

    #[ORM\Column(name: 'PO_CLOSED_BY', length: 16, nullable: true)]
    #[Groups(['position:get'])]
    private ?string $closedBy = null;

    #[ORM\Column(name: 'PO_CREATED_AT')]
    #[Groups(['position:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'PO_UPDATED_AT', nullable: true)]
    #[Groups(['position:get'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?string
    {
        return $this->id;
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

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function setDepartment(string $department): static
    {
        $this->department = $department;

        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(string $level): static
    {
        $this->level = $level;

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

    public function getHeadcount(): ?int
    {
        return $this->headcount;
    }

    public function setHeadcount(int $headcount): static
    {
        $this->headcount = $headcount;

        return $this;
    }

    public function getOpenPositions(): ?int
    {
        return $this->openPositions;
    }

    public function setOpenPositions(int $openPositions): static
    {
        $this->openPositions = $openPositions;

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
    public function getOpenedAt(): ?\DateTimeImmutable { return $this->openedAt; }
    public function setOpenedAt(?\DateTimeImmutable $openedAt): static { $this->openedAt = $openedAt; return $this; }
    public function getOpenedBy(): ?string { return $this->openedBy; }
    public function setOpenedBy(?string $openedBy): static { $this->openedBy = $openedBy; return $this; }
    public function getClosedAt(): ?\DateTimeImmutable { return $this->closedAt; }
    public function setClosedAt(?\DateTimeImmutable $closedAt): static { $this->closedAt = $closedAt; return $this; }
    public function getClosedBy(): ?string { return $this->closedBy; }
    public function setClosedBy(?string $closedBy): static { $this->closedBy = $closedBy; return $this; }

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
