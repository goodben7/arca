<?php

namespace App\Entity;

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
use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use App\Doctrine\IdGenerator;
use App\Dto\ApproveRecruitmentRequestDto;
use App\Dto\CreateRecruitmentRequestDto;
use App\Dto\RejectRecruitmentRequestDto;
use App\Model\RecruitmentRequestConstants;
use App\Model\RessourceInterface;
use App\Repository\RecruitmentRequestRepository;
use App\State\ApproveRecruitmentRequestProcessor;
use App\State\CreateRecruitmentRequestProcessor;
use App\State\RejectRecruitmentRequestProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RecruitmentRequestRepository::class)] 
#[ORM\Table(name: '`recruitment_request`')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'recruitment_request:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_RECRUITMENT_REQUEST_DETAILS")',
            provider: ItemProvider::class
        ),
        new GetCollection(
            security: 'is_granted("ROLE_RECRUITMENT_REQUEST_LIST")',
            provider: CollectionProvider::class
        ),
        new Post(
            security: 'is_granted("ROLE_RECRUITMENT_REQUEST_CREATE")',
            input: CreateRecruitmentRequestDto::class,
            processor: CreateRecruitmentRequestProcessor::class
        ),
        new Patch(
            security: 'is_granted("ROLE_RECRUITMENT_REQUEST_UPDATE")',
            denormalizationContext: ['groups' => 'recruitment_request:patch',],
            processor: PersistProcessor::class,
        ),
        new Post(
            uriTemplate: '/recruitment_requests/approvals',
            security: 'is_granted("ROLE_RECRUITMENT_REQUEST_APPROVE")',
            input: ApproveRecruitmentRequestDto::class,
            processor: ApproveRecruitmentRequestProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/recruitment_requests/rejections',
            security: 'is_granted("ROLE_RECRUITMENT_REQUEST_REJECT")',
            input: RejectRecruitmentRequestDto::class,
            processor: RejectRecruitmentRequestProcessor::class,
            status: 200
        ),
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'department' => 'exact',
    'requestedBy' => 'exact',
    'position' => 'exact',
    'status' => 'exact',
    'approvedBy' => 'exact',
    'rejectedBy' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt'])]
class RecruitmentRequest implements RessourceInterface
{
    public const string ID_PREFIX = 'RR';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'RR_ID', length: 16)]
    #[Groups(['recruitment_request:get'])]
    private ?string $id = null;

    #[ORM\Column(name: 'RR_DEPARTMENT', length: 16)]
    #[Groups(['recruitment_request:get'])]
    #[Assert\NotBlank]
    private ?string $department = null;

    #[ORM\Column(name: 'RR_REQUESTED_BY', length: 16)]
    #[Groups(['recruitment_request:get'])]
    #[Assert\NotBlank]
    private ?string $requestedBy = null;

    #[ORM\Column(name: 'RR_POSITION', length: 16)]
    #[Groups(['recruitment_request:get'])]
    #[Assert\NotBlank]
    private ?string $position = null;

    #[ORM\Column(name: 'RR_NUMBER_OF_POSITIONS')]
    #[Groups(['recruitment_request:get', 'recruitment_request:patch'])]
    #[Assert\Positive]
    #[Assert\NotNull]
    private ?int $numberOfPositions = null;

    #[ORM\Column(name: 'RR_JUSTIFICATION', type: Types::TEXT)]
    #[Groups(['recruitment_request:get', 'recruitment_request:patch'])]
    #[Assert\NotBlank]
    private ?string $justification = null;

    #[ORM\Column(name: 'RR_STATUS', length: 15)]
    #[Groups(['recruitment_request:get'])]
    #[Assert\Choice(callback: [RecruitmentRequestConstants::class, 'getStatuses'])]
    #[Assert\NotBlank]
    private ?string $status = null;

    #[ORM\Column(name: 'RR_APPROVED_BY', length: 16, nullable: true)]
    #[Groups(['recruitment_request:get'])]
    private ?string $approvedBy = null;

    #[ORM\Column(name: 'RR_APPROVED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['recruitment_request:get'])]
    private ?\DateTimeImmutable $approvedAt = null;

    #[ORM\Column(name: 'RR_REJECTED_BY', length: 16, nullable: true)]
    #[Groups(['recruitment_request:get'])]
    private ?string $rejectedBy = null;

    #[ORM\Column(name: 'RR_REJECTED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['recruitment_request:get'])]
    private ?\DateTimeImmutable $rejectedAt = null;

    #[ORM\Column(name: 'RR_REJECTION_REASON', type: Types::TEXT, nullable: true)]
    #[Groups(['recruitment_request:get'])]
    private ?string $rejectionReason = null;

    #[ORM\Column(name: 'RR_CREATED_AT')]
    #[Groups(['recruitment_request:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?string { return $this->id; }
    public function getDepartment(): ?string { return $this->department; }
    public function setDepartment(string $department): static { $this->department = $department; return $this; }
    public function getRequestedBy(): ?string { return $this->requestedBy; }
    public function setRequestedBy(string $requestedBy): static { $this->requestedBy = $requestedBy; return $this; }
    public function getPosition(): ?string { return $this->position; }
    public function setPosition(string $position): static { $this->position = $position; return $this; }
    public function getNumberOfPositions(): ?int { return $this->numberOfPositions; }
    public function setNumberOfPositions(int $numberOfPositions): static { $this->numberOfPositions = $numberOfPositions; return $this; }
    public function getJustification(): ?string { return $this->justification; }
    public function setJustification(string $justification): static { $this->justification = $justification; return $this; }
    public function getStatus(): ?string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }
    public function getApprovedBy(): ?string { return $this->approvedBy; }
    public function setApprovedBy(?string $approvedBy): static { $this->approvedBy = $approvedBy; return $this; }
    public function getApprovedAt(): ?\DateTimeImmutable { return $this->approvedAt; }
    public function setApprovedAt(?\DateTimeImmutable $approvedAt): static { $this->approvedAt = $approvedAt; return $this; }
    public function getRejectedBy(): ?string { return $this->rejectedBy; }
    public function setRejectedBy(?string $rejectedBy): static { $this->rejectedBy = $rejectedBy; return $this; }
    public function getRejectedAt(): ?\DateTimeImmutable { return $this->rejectedAt; }
    public function setRejectedAt(?\DateTimeImmutable $rejectedAt): static { $this->rejectedAt = $rejectedAt; return $this; }
    public function getRejectionReason(): ?string { return $this->rejectionReason; }
    public function setRejectionReason(?string $rejectionReason): static { $this->rejectionReason = $rejectionReason; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }

    #[ORM\PrePersist]
    public function initCreatedAt(): void { $this->createdAt = new \DateTimeImmutable(); }
}
