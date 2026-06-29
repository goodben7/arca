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
use App\Model\RessourceInterface;
use App\Repository\TrainingCatalogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TrainingCatalogRepository::class)]
#[ORM\Table(name: '`training_catalog`')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'training_catalog:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_TRAINING_CATALOG_DETAILS")',
            provider: ItemProvider::class,
        ),
        new GetCollection(
            security: 'is_granted("ROLE_TRAINING_CATALOG_LIST")',
            provider: CollectionProvider::class,
        ),
        new Post(
            security: 'is_granted("ROLE_TRAINING_CATALOG_CREATE")',
            denormalizationContext: ['groups' => 'training_catalog:post'],
            processor: PersistProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_TRAINING_CATALOG_UPDATE")',
            denormalizationContext: ['groups' => 'training_catalog:patch'],
            processor: PersistProcessor::class,
        ),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'title' => 'partial',
    'provider' => 'partial',
])]
#[ApiFilter(OrderFilter::class, properties: ['title', 'duration', 'cost', 'createdAt'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt'])]
class TrainingCatalog implements RessourceInterface
{
    public const string ID_PREFIX = 'TC';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'TC_ID', length: 16)]
    #[Groups(['training_catalog:get', 'training_session:get', 'job_role_required_training:get'])]
    private ?string $id = null;

    #[ORM\Column(name: 'TC_TITLE', length: 160)]
    #[Groups(['training_catalog:get', 'training_catalog:post', 'training_catalog:patch'])]
    #[Assert\NotBlank]
    private ?string $title = null;

    #[ORM\Column(name: 'TC_DESCRIPTION', type: Types::TEXT, nullable: true)]
    #[Groups(['training_catalog:get', 'training_catalog:post', 'training_catalog:patch'])]
    private ?string $description = null;

    #[ORM\Column(name: 'TC_PROVIDER', length: 120)]
    #[Groups(['training_catalog:get', 'training_catalog:post', 'training_catalog:patch'])]
    #[Assert\NotBlank]
    private ?string $provider = null;

    #[ORM\Column(name: 'TC_DURATION')]
    #[Groups(['training_catalog:get', 'training_catalog:post', 'training_catalog:patch'])]
    #[Assert\Positive]
    #[Assert\NotNull]
    private ?int $duration = null;

    #[ORM\Column(name: 'TC_COST', type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['training_catalog:get', 'training_catalog:post', 'training_catalog:patch'])]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    private ?string $cost = null;

    #[ORM\Column(name: 'TC_CREATED_AT')]
    #[Groups(['training_catalog:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'TC_UPDATED_AT', nullable: true)]
    #[Groups(['training_catalog:get'])]
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): static
    {
        $this->provider = $provider;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getCost(): ?string
    {
        return $this->cost;
    }

    public function setCost(string $cost): static
    {
        $this->cost = $cost;

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
