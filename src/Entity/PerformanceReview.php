<?php

namespace App\Entity;

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
use App\Dto\CreatePerformanceReviewDto;
use App\Dto\SubmitPerformanceReviewDto;
use App\Dto\ValidatePerformanceReviewDto;
use App\Model\PerformanceReviewConstants;
use App\Model\RessourceInterface;
use App\Repository\PerformanceReviewRepository;
use App\State\CreatePerformanceReviewProcessor;
use App\State\SubmitPerformanceReviewProcessor;
use App\State\ValidatePerformanceReviewProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PerformanceReviewRepository::class)]
#[ORM\Table(name: '`performance_review`')]
#[ORM\UniqueConstraint(name: 'UNIQ_PERFORMANCE_REVIEW_EMPLOYEE_CYCLE', fields: ['employee', 'cycle'])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'performance_review:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_PERFORMANCE_REVIEW_DETAILS")',
            provider: ItemProvider::class,
        ),
        new GetCollection(
            security: 'is_granted("ROLE_PERFORMANCE_REVIEW_LIST")',
            provider: CollectionProvider::class,
        ),
        new Post(
            security: 'is_granted("ROLE_PERFORMANCE_REVIEW_CREATE")',
            input: CreatePerformanceReviewDto::class,
            processor: CreatePerformanceReviewProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_PERFORMANCE_REVIEW_UPDATE")',
            denormalizationContext: ['groups' => 'performance_review:patch'],
            processor: \ApiPlatform\Doctrine\Common\State\PersistProcessor::class,
        ),
        new Post(
            uriTemplate: '/performance_reviews/submissions',
            security: 'is_granted("ROLE_PERFORMANCE_REVIEW_SUBMIT")',
            input: SubmitPerformanceReviewDto::class,
            processor: SubmitPerformanceReviewProcessor::class,
            status: 200,
        ),
        new Post(
            uriTemplate: '/performance_reviews/validations',
            security: 'is_granted("ROLE_PERFORMANCE_REVIEW_VALIDATE")',
            input: ValidatePerformanceReviewDto::class,
            processor: ValidatePerformanceReviewProcessor::class,
            status: 200,
        ),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'employee' => 'exact',
    'cycle' => 'exact',
    'status' => 'exact',
    'reviewer' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['score', 'createdAt', 'validatedAt'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'submittedAt', 'validatedAt'])]
class PerformanceReview implements RessourceInterface
{
    public const string ID_PREFIX = 'PV';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'PV_ID', length: 16)]
    #[Groups(['performance_review:get'])]
    private ?string $id = null;

    #[ORM\Column(name: 'PV_EMPLOYEE', length: 16)]
    #[Groups(['performance_review:get'])]
    #[Assert\NotBlank]
    private ?string $employee = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'PV_CYCLE', referencedColumnName: 'EC_ID', nullable: false)]
    #[Groups(['performance_review:get'])]
    #[Assert\NotNull]
    private ?EvaluationCycle $cycle = null;

    #[ORM\Column(name: 'PV_REVIEWER', length: 16, nullable: true)]
    #[Groups(['performance_review:get', 'performance_review:patch'])]
    private ?string $reviewer = null;

    #[ORM\Column(name: 'PV_SCORE', type: Types::DECIMAL, precision: 4, scale: 2, nullable: true)]
    #[Groups(['performance_review:get', 'performance_review:patch'])]
    #[Assert\Range(min: 0, max: 5)]
    private ?string $score = null;

    #[ORM\Column(name: 'PV_COMMENT', type: Types::TEXT, nullable: true)]
    #[Groups(['performance_review:get', 'performance_review:patch'])]
    private ?string $comment = null;

    #[ORM\Column(name: 'PV_STATUS', length: 15)]
    #[Groups(['performance_review:get'])]
    #[Assert\Choice(callback: [PerformanceReviewConstants::class, 'getStatuses'])]
    private ?string $status = null;

    #[ORM\Column(name: 'PV_SUBMITTED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['performance_review:get'])]
    private ?\DateTimeImmutable $submittedAt = null;

    #[ORM\Column(name: 'PV_VALIDATED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['performance_review:get'])]
    private ?\DateTimeImmutable $validatedAt = null;

    #[ORM\Column(name: 'PV_VALIDATED_BY', length: 16, nullable: true)]
    #[Groups(['performance_review:get'])]
    private ?string $validatedBy = null;

    #[ORM\Column(name: 'PV_CREATED_AT')]
    #[Groups(['performance_review:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'PV_UPDATED_AT', nullable: true)]
    #[Groups(['performance_review:get'])]
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

    public function getReviewer(): ?string
    {
        return $this->reviewer;
    }

    public function setReviewer(?string $reviewer): static
    {
        $this->reviewer = $reviewer;

        return $this;
    }

    public function getScore(): ?string
    {
        return $this->score;
    }

    public function setScore(?string $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

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

    public function getSubmittedAt(): ?\DateTimeImmutable
    {
        return $this->submittedAt;
    }

    public function setSubmittedAt(?\DateTimeImmutable $submittedAt): static
    {
        $this->submittedAt = $submittedAt;

        return $this;
    }

    public function getValidatedAt(): ?\DateTimeImmutable
    {
        return $this->validatedAt;
    }

    public function setValidatedAt(?\DateTimeImmutable $validatedAt): static
    {
        $this->validatedAt = $validatedAt;

        return $this;
    }

    public function getValidatedBy(): ?string
    {
        return $this->validatedBy;
    }

    public function setValidatedBy(?string $validatedBy): static
    {
        $this->validatedBy = $validatedBy;

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
