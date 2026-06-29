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
use App\Repository\CareerPathRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CareerPathRepository::class)]
#[ORM\Table(name: '`career_path`')]
#[ORM\UniqueConstraint(name: 'UNIQ_CAREER_PATH_TRANSITION', fields: ['fromJobRole', 'toJobRole'])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'career_path:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_CAREER_PATH_DETAILS")',
            provider: ItemProvider::class,
        ),
        new GetCollection(
            security: 'is_granted("ROLE_CAREER_PATH_LIST")',
            provider: CollectionProvider::class,
        ),
        new Post(
            security: 'is_granted("ROLE_CAREER_PATH_CREATE")',
            denormalizationContext: ['groups' => 'career_path:post'],
            processor: PersistProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_CAREER_PATH_UPDATE")',
            denormalizationContext: ['groups' => 'career_path:patch'],
            processor: PersistProcessor::class,
        ),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'fromJobRole' => 'exact',
    'toJobRole' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'updatedAt'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'updatedAt'])]
class CareerPath implements RessourceInterface
{
    public const string ID_PREFIX = 'CP';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'CP_ID', length: 16)]
    #[Groups(['career_path:get'])]
    private ?string $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'CP_FROM_JOB_ROLE', referencedColumnName: 'JR_ID', nullable: false)]
    #[Groups(['career_path:get', 'career_path:post', 'career_path:patch'])]
    #[Assert\NotNull]
    private ?JobRole $fromJobRole = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'CP_TO_JOB_ROLE', referencedColumnName: 'JR_ID', nullable: false)]
    #[Groups(['career_path:get', 'career_path:post', 'career_path:patch'])]
    #[Assert\NotNull]
    private ?JobRole $toJobRole = null;

    #[ORM\Column(name: 'CP_CONDITIONS', type: Types::JSON, nullable: true)]
    #[Groups(['career_path:get', 'career_path:post', 'career_path:patch'])]
    private ?array $conditions = null;

    #[ORM\Column(name: 'CP_CREATED_AT')]
    #[Groups(['career_path:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'CP_UPDATED_AT', nullable: true)]
    #[Groups(['career_path:get'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getFromJobRole(): ?JobRole
    {
        return $this->fromJobRole;
    }

    public function setFromJobRole(JobRole $fromJobRole): static
    {
        $this->fromJobRole = $fromJobRole;

        return $this;
    }

    public function getToJobRole(): ?JobRole
    {
        return $this->toJobRole;
    }

    public function setToJobRole(JobRole $toJobRole): static
    {
        $this->toJobRole = $toJobRole;

        return $this;
    }

    public function getConditions(): ?array
    {
        return $this->conditions;
    }

    public function setConditions(?array $conditions): static
    {
        $this->conditions = $conditions;

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
