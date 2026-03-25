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
use ApiPlatform\Metadata\Post;
use App\Doctrine\IdGenerator;
use App\Dto\CreateTrainingRequestDto;
use App\Dto\ApproveTrainingRequestDto;
use App\Dto\RejectTrainingRequestDto;
use App\Model\RessourceInterface;
use App\Model\TrainingRequestConstants;
use App\Repository\TrainingRequestRepository;
use App\State\CreateTrainingRequestProcessor;
use App\State\ApproveTrainingRequestProcessor;
use App\State\RejectTrainingRequestProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TrainingRequestRepository::class)]
#[ORM\Table(name: '`training_request`')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'training_request:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_TRAINING_REQUEST_DETAILS")',
            provider: ItemProvider::class
        ),
        new GetCollection(
            security: 'is_granted("ROLE_TRAINING_REQUEST_LIST")',
            provider: CollectionProvider::class
        ),
        new Post(
            security: 'is_granted("ROLE_TRAINING_REQUEST_CREATE")',
            input: CreateTrainingRequestDto::class,
            processor: CreateTrainingRequestProcessor::class
        ),
        new Post(
            uriTemplate: '/training_requests/approvals',
            security: 'is_granted("ROLE_TRAINING_REQUEST_APPROVE")',
            input: ApproveTrainingRequestDto::class,
            processor: ApproveTrainingRequestProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/training_requests/rejections',
            security: 'is_granted("ROLE_TRAINING_REQUEST_REJECT")',
            input: RejectTrainingRequestDto::class,
            processor: RejectTrainingRequestProcessor::class,
            status: 200
        ),
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'department' => 'exact',
    'requestedBy' => 'exact',
    'status' => 'exact',
    'approvedBy' => 'exact',
    'rejectedBy' => 'exact',
    'priority' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt'])]
class TrainingRequest implements RessourceInterface
{
    public const string ID_PREFIX = 'TR';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'TR_ID', length: 16)]
    #[Groups(['training_request:get'])]
    private ?string $id = null;

    #[ORM\Column(name: 'TR_DEPARTMENT', length: 16)]
    #[Groups(['training_request:get'])]
    #[Assert\NotBlank]
    private ?string $department = null;

    #[ORM\Column(name: 'TR_REQUESTED_BY', length: 16)]
    #[Groups(['training_request:get'])]
    #[Assert\NotBlank]
    private ?string $requestedBy = null;

    #[ORM\Column(name: 'TR_TITLE', length: 160)]
    #[Groups(['training_request:get'])]
    #[Assert\NotBlank]
    private ?string $title = null;

    #[ORM\Column(name: 'TR_DESCRIPTION', type: Types::TEXT)]
    #[Groups(['training_request:get'])]
    #[Assert\NotBlank]
    private ?string $description = null;

    #[ORM\Column(name: 'TR_NUMBER_OF_PARTICIPANTS')]
    #[Groups(['training_request:get'])]
    #[Assert\Positive]
    #[Assert\NotNull]
    private ?int $numberOfParticipants = null;

    #[ORM\Column(name: 'TR_PRIORITY', length: 20)]
    #[Groups(['training_request:get'])]
    #[Assert\Choice(callback: [TrainingRequestConstants::class, 'getPriorities'])]
    #[Assert\NotBlank]
    private ?string $priority = null;

    #[ORM\Column(name: 'TR_STATUS', length: 15)]
    #[Groups(['training_request:get'])]
    #[Assert\Choice(callback: [TrainingRequestConstants::class, 'getStatuses'])]
    #[Assert\NotBlank]
    private ?string $status = null;

    #[ORM\Column(name: 'TR_APPROVED_BY', length: 16, nullable: true)]
    #[Groups(['training_request:get'])]
    private ?string $approvedBy = null;

    #[ORM\Column(name: 'TR_APPROVED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['training_request:get'])]
    private ?\DateTimeImmutable $approvedAt = null;

    #[ORM\Column(name: 'TR_REJECTED_BY', length: 16, nullable: true)]
    #[Groups(['training_request:get'])]
    private ?string $rejectedBy = null;

    #[ORM\Column(name: 'TR_REJECTED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['training_request:get'])]
    private ?\DateTimeImmutable $rejectedAt = null;

    #[ORM\Column(name: 'TR_REJECTION_REASON', type: Types::TEXT, nullable: true)]
    #[Groups(['training_request:get'])]
    private ?string $rejectionReason = null;

    #[ORM\Column(name: 'TR_CREATED_AT')]
    #[Groups(['training_request:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?string { return $this->id; }
    public function getDepartment(): ?string { return $this->department; }
    public function setDepartment(string $department): static { $this->department = $department; return $this; }
    public function getRequestedBy(): ?string { return $this->requestedBy; }
    public function setRequestedBy(string $requestedBy): static { $this->requestedBy = $requestedBy; return $this; }
    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(string $description): static { $this->description = $description; return $this; }
    public function getNumberOfParticipants(): ?int { return $this->numberOfParticipants; }
    public function setNumberOfParticipants(int $numberOfParticipants): static { $this->numberOfParticipants = $numberOfParticipants; return $this; }
    public function getPriority(): ?string { return $this->priority; }
    public function setPriority(string $priority): static { $this->priority = $priority; return $this; }
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
