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
use ApiPlatform\Metadata\Post;
use App\Doctrine\IdGenerator;
use App\Dto\ApproveMobilityRequestDto;
use App\Dto\CancelMobilityRequestDto;
use App\Dto\CreateMobilityRequestDto;
use App\Dto\RejectMobilityRequestDto;
use App\Dto\SubmitMobilityRequestDto;
use App\Model\MobilityRequestConstants;
use App\Model\RessourceInterface;
use App\Repository\MobilityRequestRepository;
use App\State\ApproveMobilityRequestProcessor;
use App\State\CancelMobilityRequestProcessor;
use App\State\CreateMobilityRequestProcessor;
use App\State\RejectMobilityRequestProcessor;
use App\State\SubmitMobilityRequestProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MobilityRequestRepository::class)]
#[ORM\Table(name: '`mobility_request`')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'mobility_request:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_MOBILITY_REQUEST_DETAILS")',
            provider: ItemProvider::class,
        ),
        new GetCollection(
            security: 'is_granted("ROLE_MOBILITY_REQUEST_LIST")',
            provider: CollectionProvider::class,
        ),
        new Post(
            security: 'is_granted("ROLE_MOBILITY_REQUEST_CREATE")',
            input: CreateMobilityRequestDto::class,
            processor: CreateMobilityRequestProcessor::class,
        ),
        new Post(
            uriTemplate: '/mobility_requests/submissions',
            security: 'is_granted("ROLE_MOBILITY_REQUEST_SUBMIT")',
            input: SubmitMobilityRequestDto::class,
            processor: SubmitMobilityRequestProcessor::class,
            status: 200,
        ),
        new Post(
            uriTemplate: '/mobility_requests/approvals',
            security: 'is_granted("ROLE_MOBILITY_REQUEST_APPROVE")',
            input: ApproveMobilityRequestDto::class,
            processor: ApproveMobilityRequestProcessor::class,
            status: 200,
        ),
        new Post(
            uriTemplate: '/mobility_requests/rejections',
            security: 'is_granted("ROLE_MOBILITY_REQUEST_REJECT")',
            input: RejectMobilityRequestDto::class,
            processor: RejectMobilityRequestProcessor::class,
            status: 200,
        ),
        new Post(
            uriTemplate: '/mobility_requests/cancellations',
            security: 'is_granted("ROLE_MOBILITY_REQUEST_CANCEL")',
            input: CancelMobilityRequestDto::class,
            processor: CancelMobilityRequestProcessor::class,
            status: 200,
        ),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'employee' => 'exact',
    'type' => 'exact',
    'status' => 'exact',
    'targetDepartment' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'submittedAt', 'implementedAt'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'submittedAt', 'implementedAt', 'rejectedAt', 'cancelledAt'])]
class MobilityRequest implements RessourceInterface
{
    public const string ID_PREFIX = 'MB';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'MB_ID', length: 16)]
    #[Groups(['mobility_request:get'])]
    private ?string $id = null;

    #[ORM\Column(name: 'MB_EMPLOYEE', length: 16)]
    #[Groups(['mobility_request:get'])]
    #[Assert\NotBlank]
    private ?string $employee = null;

    #[ORM\Column(name: 'MB_TYPE', length: 12)]
    #[Groups(['mobility_request:get'])]
    #[Assert\Choice(callback: [MobilityRequestConstants::class, 'getTypes'])]
    #[Assert\NotBlank]
    private ?string $type = null;

    #[ORM\Column(name: 'MB_STATUS', length: 20)]
    #[Groups(['mobility_request:get'])]
    #[Assert\Choice(callback: [MobilityRequestConstants::class, 'getStatuses'])]
    #[Assert\NotBlank]
    private ?string $status = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'MB_TARGET_JOB_ROLE', referencedColumnName: 'JR_ID', nullable: true)]
    #[Groups(['mobility_request:get'])]
    private ?JobRole $targetJobRole = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'MB_TARGET_GRADE', referencedColumnName: 'GR_ID', nullable: true)]
    #[Groups(['mobility_request:get'])]
    private ?Grade $targetGrade = null;

    #[ORM\Column(name: 'MB_TARGET_DEPARTMENT', length: 120, nullable: true)]
    #[Groups(['mobility_request:get'])]
    private ?string $targetDepartment = null;

    #[ORM\Column(name: 'MB_REASON', type: Types::TEXT, nullable: true)]
    #[Groups(['mobility_request:get'])]
    private ?string $reason = null;

    #[ORM\Column(name: 'MB_SUBMITTED_AT', nullable: true)]
    #[Groups(['mobility_request:get'])]
    private ?\DateTimeImmutable $submittedAt = null;

    #[ORM\Column(name: 'MB_SUBMITTED_BY', length: 16, nullable: true)]
    #[Groups(['mobility_request:get'])]
    private ?string $submittedBy = null;

    #[ORM\Column(name: 'MB_MANAGER_APPROVED_AT', nullable: true)]
    #[Groups(['mobility_request:get'])]
    private ?\DateTimeImmutable $managerApprovedAt = null;

    #[ORM\Column(name: 'MB_MANAGER_APPROVED_BY', length: 16, nullable: true)]
    #[Groups(['mobility_request:get'])]
    private ?string $managerApprovedBy = null;

    #[ORM\Column(name: 'MB_HR_APPROVED_AT', nullable: true)]
    #[Groups(['mobility_request:get'])]
    private ?\DateTimeImmutable $hrApprovedAt = null;

    #[ORM\Column(name: 'MB_HR_APPROVED_BY', length: 16, nullable: true)]
    #[Groups(['mobility_request:get'])]
    private ?string $hrApprovedBy = null;

    #[ORM\Column(name: 'MB_EXECUTIVE_APPROVED_AT', nullable: true)]
    #[Groups(['mobility_request:get'])]
    private ?\DateTimeImmutable $executiveApprovedAt = null;

    #[ORM\Column(name: 'MB_EXECUTIVE_APPROVED_BY', length: 16, nullable: true)]
    #[Groups(['mobility_request:get'])]
    private ?string $executiveApprovedBy = null;

    #[ORM\Column(name: 'MB_IMPLEMENTED_AT', nullable: true)]
    #[Groups(['mobility_request:get'])]
    private ?\DateTimeImmutable $implementedAt = null;

    #[ORM\Column(name: 'MB_IMPLEMENTED_BY', length: 16, nullable: true)]
    #[Groups(['mobility_request:get'])]
    private ?string $implementedBy = null;

    #[ORM\Column(name: 'MB_REJECTED_AT', nullable: true)]
    #[Groups(['mobility_request:get'])]
    private ?\DateTimeImmutable $rejectedAt = null;

    #[ORM\Column(name: 'MB_REJECTED_BY', length: 16, nullable: true)]
    #[Groups(['mobility_request:get'])]
    private ?string $rejectedBy = null;

    #[ORM\Column(name: 'MB_REJECTION_REASON', type: Types::TEXT, nullable: true)]
    #[Groups(['mobility_request:get'])]
    private ?string $rejectionReason = null;

    #[ORM\Column(name: 'MB_CANCELLED_AT', nullable: true)]
    #[Groups(['mobility_request:get'])]
    private ?\DateTimeImmutable $cancelledAt = null;

    #[ORM\Column(name: 'MB_CANCELLED_BY', length: 16, nullable: true)]
    #[Groups(['mobility_request:get'])]
    private ?string $cancelledBy = null;

    #[ORM\Column(name: 'MB_CREATED_AT')]
    #[Groups(['mobility_request:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'MB_UPDATED_AT', nullable: true)]
    #[Groups(['mobility_request:get'])]
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

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

    public function getTargetJobRole(): ?JobRole
    {
        return $this->targetJobRole;
    }

    public function setTargetJobRole(?JobRole $targetJobRole): static
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

    public function getTargetDepartment(): ?string
    {
        return $this->targetDepartment;
    }

    public function setTargetDepartment(?string $targetDepartment): static
    {
        $this->targetDepartment = $targetDepartment;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): static
    {
        $this->reason = $reason;

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

    public function getSubmittedBy(): ?string
    {
        return $this->submittedBy;
    }

    public function setSubmittedBy(?string $submittedBy): static
    {
        $this->submittedBy = $submittedBy;

        return $this;
    }

    public function getManagerApprovedAt(): ?\DateTimeImmutable
    {
        return $this->managerApprovedAt;
    }

    public function setManagerApprovedAt(?\DateTimeImmutable $managerApprovedAt): static
    {
        $this->managerApprovedAt = $managerApprovedAt;

        return $this;
    }

    public function getManagerApprovedBy(): ?string
    {
        return $this->managerApprovedBy;
    }

    public function setManagerApprovedBy(?string $managerApprovedBy): static
    {
        $this->managerApprovedBy = $managerApprovedBy;

        return $this;
    }

    public function getHrApprovedAt(): ?\DateTimeImmutable
    {
        return $this->hrApprovedAt;
    }

    public function setHrApprovedAt(?\DateTimeImmutable $hrApprovedAt): static
    {
        $this->hrApprovedAt = $hrApprovedAt;

        return $this;
    }

    public function getHrApprovedBy(): ?string
    {
        return $this->hrApprovedBy;
    }

    public function setHrApprovedBy(?string $hrApprovedBy): static
    {
        $this->hrApprovedBy = $hrApprovedBy;

        return $this;
    }

    public function getExecutiveApprovedAt(): ?\DateTimeImmutable
    {
        return $this->executiveApprovedAt;
    }

    public function setExecutiveApprovedAt(?\DateTimeImmutable $executiveApprovedAt): static
    {
        $this->executiveApprovedAt = $executiveApprovedAt;

        return $this;
    }

    public function getExecutiveApprovedBy(): ?string
    {
        return $this->executiveApprovedBy;
    }

    public function setExecutiveApprovedBy(?string $executiveApprovedBy): static
    {
        $this->executiveApprovedBy = $executiveApprovedBy;

        return $this;
    }

    public function getImplementedAt(): ?\DateTimeImmutable
    {
        return $this->implementedAt;
    }

    public function setImplementedAt(?\DateTimeImmutable $implementedAt): static
    {
        $this->implementedAt = $implementedAt;

        return $this;
    }

    public function getImplementedBy(): ?string
    {
        return $this->implementedBy;
    }

    public function setImplementedBy(?string $implementedBy): static
    {
        $this->implementedBy = $implementedBy;

        return $this;
    }

    public function getRejectedAt(): ?\DateTimeImmutable
    {
        return $this->rejectedAt;
    }

    public function setRejectedAt(?\DateTimeImmutable $rejectedAt): static
    {
        $this->rejectedAt = $rejectedAt;

        return $this;
    }

    public function getRejectedBy(): ?string
    {
        return $this->rejectedBy;
    }

    public function setRejectedBy(?string $rejectedBy): static
    {
        $this->rejectedBy = $rejectedBy;

        return $this;
    }

    public function getRejectionReason(): ?string
    {
        return $this->rejectionReason;
    }

    public function setRejectionReason(?string $rejectionReason): static
    {
        $this->rejectionReason = $rejectionReason;

        return $this;
    }

    public function getCancelledAt(): ?\DateTimeImmutable
    {
        return $this->cancelledAt;
    }

    public function setCancelledAt(?\DateTimeImmutable $cancelledAt): static
    {
        $this->cancelledAt = $cancelledAt;

        return $this;
    }

    public function getCancelledBy(): ?string
    {
        return $this->cancelledBy;
    }

    public function setCancelledBy(?string $cancelledBy): static
    {
        $this->cancelledBy = $cancelledBy;

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
