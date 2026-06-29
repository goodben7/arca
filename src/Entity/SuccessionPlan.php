<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
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
use App\Dto\CreateSuccessionPlanDto;
use App\Model\RessourceInterface;
use App\Model\SuccessionPlanConstants;
use App\Repository\SuccessionPlanRepository;
use App\State\CreateSuccessionPlanProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SuccessionPlanRepository::class)]
#[ORM\Table(name: '`succession_plan`')]
#[ORM\UniqueConstraint(name: 'UNIQ_SP_CRITICAL_CANDIDATE', fields: ['criticalJobRole', 'candidate'])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'succession_plan:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_SUCCESSION_PLAN_DETAILS")',
            provider: ItemProvider::class,
        ),
        new GetCollection(
            security: 'is_granted("ROLE_SUCCESSION_PLAN_LIST")',
            provider: CollectionProvider::class,
        ),
        new Post(
            security: 'is_granted("ROLE_SUCCESSION_PLAN_CREATE")',
            input: CreateSuccessionPlanDto::class,
            processor: CreateSuccessionPlanProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_SUCCESSION_PLAN_UPDATE")',
            denormalizationContext: ['groups' => 'succession_plan:patch'],
            processor: PersistProcessor::class,
        ),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'criticalJobRole' => 'exact',
    'candidate' => 'exact',
    'readinessLevel' => 'exact',
    'status' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'readinessLevel'])]
class SuccessionPlan implements RessourceInterface
{
    public const string ID_PREFIX = 'SP';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'SP_ID', length: 16)]
    #[Groups(['succession_plan:get'])]
    private ?string $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'SP_CRITICAL_JOB_ROLE', referencedColumnName: 'JR_ID', nullable: false)]
    #[Groups(['succession_plan:get'])]
    #[Assert\NotNull]
    private ?JobRole $criticalJobRole = null;

    #[ORM\Column(name: 'SP_CANDIDATE', length: 16)]
    #[Groups(['succession_plan:get'])]
    #[Assert\NotBlank]
    private ?string $candidate = null;

    #[ORM\Column(name: 'SP_READINESS_LEVEL', length: 25)]
    #[Groups(['succession_plan:get', 'succession_plan:patch'])]
    #[Assert\Choice(callback: [SuccessionPlanConstants::class, 'getReadinessLevels'])]
    private ?string $readinessLevel = null;

    #[ORM\Column(name: 'SP_STATUS', length: 10)]
    #[Groups(['succession_plan:get', 'succession_plan:patch'])]
    #[Assert\Choice(callback: [SuccessionPlanConstants::class, 'getStatuses'])]
    private ?string $status = null;

    #[ORM\Column(name: 'SP_NOTES', type: Types::TEXT, nullable: true)]
    #[Groups(['succession_plan:get', 'succession_plan:patch'])]
    private ?string $notes = null;

    #[ORM\Column(name: 'SP_CREATED_AT')]
    #[Groups(['succession_plan:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'SP_UPDATED_AT', nullable: true)]
    #[Groups(['succession_plan:get'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getCriticalJobRole(): ?JobRole
    {
        return $this->criticalJobRole;
    }

    public function setCriticalJobRole(JobRole $criticalJobRole): static
    {
        $this->criticalJobRole = $criticalJobRole;

        return $this;
    }

    public function getCandidate(): ?string
    {
        return $this->candidate;
    }

    public function setCandidate(string $candidate): static
    {
        $this->candidate = $candidate;

        return $this;
    }

    public function getReadinessLevel(): ?string
    {
        return $this->readinessLevel;
    }

    public function setReadinessLevel(string $readinessLevel): static
    {
        $this->readinessLevel = $readinessLevel;

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

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

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
