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
use App\Dto\CreateCareerPlanDto;
use App\Model\CareerPlanConstants;
use App\Model\RessourceInterface;
use App\Repository\CareerPlanRepository;
use App\State\CreateCareerPlanProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CareerPlanRepository::class)]
#[ORM\Table(name: '`career_plan`')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'career_plan:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_CAREER_PLAN_DETAILS")',
            provider: ItemProvider::class,
        ),
        new GetCollection(
            security: 'is_granted("ROLE_CAREER_PLAN_LIST")',
            provider: CollectionProvider::class,
        ),
        new Post(
            security: 'is_granted("ROLE_CAREER_PLAN_CREATE")',
            input: CreateCareerPlanDto::class,
            processor: CreateCareerPlanProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_CAREER_PLAN_UPDATE")',
            denormalizationContext: ['groups' => 'career_plan:patch'],
            processor: PersistProcessor::class,
        ),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'employee' => 'exact',
    'targetJobRole' => 'exact',
    'targetGrade' => 'exact',
    'status' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['targetDate', 'createdAt'])]
#[ApiFilter(DateFilter::class, properties: ['targetDate', 'createdAt'])]
class CareerPlan implements RessourceInterface
{
    public const string ID_PREFIX = 'PL';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'PL_ID', length: 16)]
    #[Groups(['career_plan:get'])]
    private ?string $id = null;

    #[ORM\Column(name: 'PL_EMPLOYEE', length: 16)]
    #[Groups(['career_plan:get'])]
    #[Assert\NotBlank]
    private ?string $employee = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'PL_TARGET_JOB_ROLE', referencedColumnName: 'JR_ID', nullable: false)]
    #[Groups(['career_plan:get'])]
    #[Assert\NotNull]
    private ?JobRole $targetJobRole = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'PL_TARGET_GRADE', referencedColumnName: 'GR_ID', nullable: true)]
    #[Groups(['career_plan:get', 'career_plan:patch'])]
    private ?Grade $targetGrade = null;

    #[ORM\Column(name: 'PL_TARGET_DATE', type: Types::DATE_IMMUTABLE)]
    #[Groups(['career_plan:get', 'career_plan:patch'])]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $targetDate = null;

    #[ORM\Column(name: 'PL_STATUS', length: 15)]
    #[Groups(['career_plan:get', 'career_plan:patch'])]
    #[Assert\Choice(callback: [CareerPlanConstants::class, 'getStatuses'])]
    private ?string $status = null;

    #[ORM\Column(name: 'PL_NOTES', type: Types::TEXT, nullable: true)]
    #[Groups(['career_plan:get', 'career_plan:patch'])]
    private ?string $notes = null;

    #[ORM\Column(name: 'PL_CREATED_AT')]
    #[Groups(['career_plan:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'PL_UPDATED_AT', nullable: true)]
    #[Groups(['career_plan:get'])]
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

    public function getTargetJobRole(): ?JobRole
    {
        return $this->targetJobRole;
    }

    public function setTargetJobRole(JobRole $targetJobRole): static
    {
        $this->targetJobRole = $targetJobRole;

        return $this;
    }

    public function getTargetGrade(): ?Grade
    {
        return $this->targetGrade;
    }

    public function setTargetGrade(?Grade $targetGrade): static
    {
        $this->targetGrade = $targetGrade;

        return $this;
    }

    public function getTargetDate(): ?\DateTimeImmutable
    {
        return $this->targetDate;
    }

    public function setTargetDate(\DateTimeImmutable $targetDate): static
    {
        $this->targetDate = $targetDate;

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
