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
use App\Dto\ApplyDisciplinarySanctionDto;
use App\Dto\CancelDisciplinaryCaseDto;
use App\Dto\CloseDisciplinaryCaseDto;
use App\Dto\CreateDisciplinaryCaseDto;
use App\Dto\DecideDisciplinaryCaseDto;
use App\Dto\OpenDisciplinaryCaseDto;
use App\Dto\RejectDisciplinaryCaseDto;
use App\Dto\RequestDisciplinaryExplanationDto;
use App\Dto\ScheduleDisciplinaryHearingDto;
use App\Model\DisciplinaryCaseConstants;
use App\Model\RessourceInterface;
use App\Repository\DisciplinaryCaseRepository;
use App\State\ApplyDisciplinarySanctionProcessor;
use App\State\CancelDisciplinaryCaseProcessor;
use App\State\CloseDisciplinaryCaseProcessor;
use App\State\CreateDisciplinaryCaseProcessor;
use App\State\DecideDisciplinaryCaseProcessor;
use App\State\OpenDisciplinaryCaseProcessor;
use App\State\RejectDisciplinaryCaseProcessor;
use App\State\RequestDisciplinaryExplanationProcessor;
use App\State\ScheduleDisciplinaryHearingProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DisciplinaryCaseRepository::class)]
#[ORM\Table(name: '`disciplinary_case`')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'disciplinary_case:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_DISCIPLINARY_CASE_DETAILS")',
            provider: ItemProvider::class,
        ),
        new GetCollection(
            security: 'is_granted("ROLE_DISCIPLINARY_CASE_LIST")',
            provider: CollectionProvider::class,
        ),
        new Post(
            security: 'is_granted("ROLE_DISCIPLINARY_CASE_CREATE")',
            input: CreateDisciplinaryCaseDto::class,
            processor: CreateDisciplinaryCaseProcessor::class,
        ),
        new Post(
            uriTemplate: '/disciplinary_cases/openings',
            security: 'is_granted("ROLE_DISCIPLINARY_CASE_OPEN")',
            input: OpenDisciplinaryCaseDto::class,
            processor: OpenDisciplinaryCaseProcessor::class,
            status: 200,
        ),
        new Post(
            uriTemplate: '/disciplinary_cases/explanations',
            security: 'is_granted("ROLE_DISCIPLINARY_CASE_REQUEST_EXPLANATION")',
            input: RequestDisciplinaryExplanationDto::class,
            processor: RequestDisciplinaryExplanationProcessor::class,
            status: 200,
        ),
        new Post(
            uriTemplate: '/disciplinary_cases/hearings',
            security: 'is_granted("ROLE_DISCIPLINARY_CASE_SCHEDULE_HEARING")',
            input: ScheduleDisciplinaryHearingDto::class,
            processor: ScheduleDisciplinaryHearingProcessor::class,
            status: 200,
        ),
        new Post(
            uriTemplate: '/disciplinary_cases/decisions',
            security: 'is_granted("ROLE_DISCIPLINARY_CASE_DECIDE")',
            input: DecideDisciplinaryCaseDto::class,
            processor: DecideDisciplinaryCaseProcessor::class,
            status: 200,
        ),
        new Post(
            uriTemplate: '/disciplinary_cases/applications',
            security: 'is_granted("ROLE_DISCIPLINARY_CASE_APPLY")',
            input: ApplyDisciplinarySanctionDto::class,
            inputFormats: [
                'json' => ['application/json'],
                'multipart' => ['multipart/form-data'],
            ],
            processor: ApplyDisciplinarySanctionProcessor::class,
            status: 200,
        ),
        new Post(
            uriTemplate: '/disciplinary_cases/cancellations',
            security: 'is_granted("ROLE_DISCIPLINARY_CASE_CANCEL")',
            input: CancelDisciplinaryCaseDto::class,
            processor: CancelDisciplinaryCaseProcessor::class,
            status: 200,
        ),
        new Post(
            uriTemplate: '/disciplinary_cases/rejections',
            security: 'is_granted("ROLE_DISCIPLINARY_CASE_REJECT")',
            input: RejectDisciplinaryCaseDto::class,
            processor: RejectDisciplinaryCaseProcessor::class,
            status: 200,
        ),
        new Post(
            uriTemplate: '/disciplinary_cases/closures',
            security: 'is_granted("ROLE_DISCIPLINARY_CASE_CLOSE")',
            input: CloseDisciplinaryCaseDto::class,
            processor: CloseDisciplinaryCaseProcessor::class,
            status: 200,
        ),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'employee' => 'exact',
    'status' => 'exact',
    'sanctionScale' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['occurredAt', 'createdAt', 'hearingAt'])]
#[ApiFilter(DateFilter::class, properties: ['occurredAt', 'createdAt', 'hearingAt'])]
class DisciplinaryCase implements RessourceInterface
{
    public const string ID_PREFIX = 'DS';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'DS_ID', length: 16)]
    #[Groups(['disciplinary_case:get'])]
    private ?string $id = null;

    #[ORM\Column(name: 'DS_EMPLOYEE', length: 16)]
    #[Groups(['disciplinary_case:get'])]
    #[Assert\NotBlank]
    private ?string $employee = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'DS_SANCTION_SCALE', referencedColumnName: 'SS_ID', nullable: false)]
    #[Groups(['disciplinary_case:get'])]
    #[Assert\NotNull]
    private ?SanctionScale $sanctionScale = null;

    #[ORM\Column(name: 'DS_STATUS', length: 30)]
    #[Groups(['disciplinary_case:get'])]
    #[Assert\Choice(callback: [DisciplinaryCaseConstants::class, 'getStatuses'])]
    private ?string $status = null;

    #[ORM\Column(name: 'DS_FACTS', type: Types::TEXT)]
    #[Groups(['disciplinary_case:get'])]
    #[Assert\NotBlank]
    private ?string $facts = null;

    #[ORM\Column(name: 'DS_REASON', type: Types::TEXT, nullable: true)]
    #[Groups(['disciplinary_case:get'])]
    private ?string $reason = null;

    #[ORM\Column(name: 'DS_OCCURRED_AT', type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['disciplinary_case:get'])]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $occurredAt = null;

    #[ORM\Column(name: 'DS_OPENED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['disciplinary_case:get'])]
    private ?\DateTimeImmutable $openedAt = null;

    #[ORM\Column(name: 'DS_OPENED_BY', length: 16, nullable: true)]
    #[Groups(['disciplinary_case:get'])]
    private ?string $openedBy = null;

    #[ORM\Column(name: 'DS_EXPLANATION_REQUESTED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['disciplinary_case:get'])]
    private ?\DateTimeImmutable $explanationRequestedAt = null;

    #[ORM\Column(name: 'DS_EXPLANATION_DUE_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['disciplinary_case:get'])]
    private ?\DateTimeImmutable $explanationDueAt = null;

    #[ORM\Column(name: 'DS_EXPLANATION_TEXT', type: Types::TEXT, nullable: true)]
    #[Groups(['disciplinary_case:get'])]
    private ?string $explanationText = null;

    #[ORM\Column(name: 'DS_HEARING_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['disciplinary_case:get'])]
    private ?\DateTimeImmutable $hearingAt = null;

    #[ORM\Column(name: 'DS_HEARING_BY', length: 16, nullable: true)]
    #[Groups(['disciplinary_case:get'])]
    private ?string $hearingBy = null;

    #[ORM\Column(name: 'DS_DECIDED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['disciplinary_case:get'])]
    private ?\DateTimeImmutable $decidedAt = null;

    #[ORM\Column(name: 'DS_DECIDED_BY', length: 16, nullable: true)]
    #[Groups(['disciplinary_case:get'])]
    private ?string $decidedBy = null;

    #[ORM\Column(name: 'DS_APPLIED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['disciplinary_case:get'])]
    private ?\DateTimeImmutable $appliedAt = null;

    #[ORM\Column(name: 'DS_APPLIED_BY', length: 16, nullable: true)]
    #[Groups(['disciplinary_case:get'])]
    private ?string $appliedBy = null;

    #[ORM\Column(name: 'DS_APPEAL_DEADLINE_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['disciplinary_case:get'])]
    private ?\DateTimeImmutable $appealDeadlineAt = null;

    #[ORM\Column(name: 'DS_CLOSED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['disciplinary_case:get'])]
    private ?\DateTimeImmutable $closedAt = null;

    #[ORM\Column(name: 'DS_CLOSED_BY', length: 16, nullable: true)]
    #[Groups(['disciplinary_case:get'])]
    private ?string $closedBy = null;

    #[ORM\Column(name: 'DS_CANCELLED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['disciplinary_case:get'])]
    private ?\DateTimeImmutable $cancelledAt = null;

    #[ORM\Column(name: 'DS_CANCELLED_BY', length: 16, nullable: true)]
    #[Groups(['disciplinary_case:get'])]
    private ?string $cancelledBy = null;

    #[ORM\Column(name: 'DS_REJECTED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['disciplinary_case:get'])]
    private ?\DateTimeImmutable $rejectedAt = null;

    #[ORM\Column(name: 'DS_REJECTED_BY', length: 16, nullable: true)]
    #[Groups(['disciplinary_case:get'])]
    private ?string $rejectedBy = null;

    #[ORM\Column(name: 'DS_REJECTION_REASON', type: Types::TEXT, nullable: true)]
    #[Groups(['disciplinary_case:get'])]
    private ?string $rejectionReason = null;

    #[ORM\Column(name: 'DS_CREATED_AT')]
    #[Groups(['disciplinary_case:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'DS_UPDATED_AT', nullable: true)]
    #[Groups(['disciplinary_case:get'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'DS_DOCUMENT', referencedColumnName: 'DC_ID', nullable: true)]
    #[Groups(['disciplinary_case:get'])]
    private ?Document $document = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'DS_EXIT_PROCESS', referencedColumnName: 'EP_ID', nullable: true)]
    #[Groups(['disciplinary_case:get'])]
    private ?ExitProcess $exitProcess = null;

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

    public function getSanctionScale(): ?SanctionScale
    {
        return $this->sanctionScale;
    }

    public function setSanctionScale(SanctionScale $sanctionScale): static
    {
        $this->sanctionScale = $sanctionScale;

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

    public function getFacts(): ?string
    {
        return $this->facts;
    }

    public function setFacts(string $facts): static
    {
        $this->facts = $facts;

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

    public function getOccurredAt(): ?\DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function setOccurredAt(\DateTimeImmutable $occurredAt): static
    {
        $this->occurredAt = $occurredAt;

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

    public function getOpenedBy(): ?string
    {
        return $this->openedBy;
    }

    public function setOpenedBy(?string $openedBy): static
    {
        $this->openedBy = $openedBy;

        return $this;
    }

    public function getExplanationRequestedAt(): ?\DateTimeImmutable
    {
        return $this->explanationRequestedAt;
    }

    public function setExplanationRequestedAt(?\DateTimeImmutable $explanationRequestedAt): static
    {
        $this->explanationRequestedAt = $explanationRequestedAt;

        return $this;
    }

    public function getExplanationDueAt(): ?\DateTimeImmutable
    {
        return $this->explanationDueAt;
    }

    public function setExplanationDueAt(?\DateTimeImmutable $explanationDueAt): static
    {
        $this->explanationDueAt = $explanationDueAt;

        return $this;
    }

    public function getExplanationText(): ?string
    {
        return $this->explanationText;
    }

    public function setExplanationText(?string $explanationText): static
    {
        $this->explanationText = $explanationText;

        return $this;
    }

    public function getHearingAt(): ?\DateTimeImmutable
    {
        return $this->hearingAt;
    }

    public function setHearingAt(?\DateTimeImmutable $hearingAt): static
    {
        $this->hearingAt = $hearingAt;

        return $this;
    }

    public function getHearingBy(): ?string
    {
        return $this->hearingBy;
    }

    public function setHearingBy(?string $hearingBy): static
    {
        $this->hearingBy = $hearingBy;

        return $this;
    }

    public function getDecidedAt(): ?\DateTimeImmutable
    {
        return $this->decidedAt;
    }

    public function setDecidedAt(?\DateTimeImmutable $decidedAt): static
    {
        $this->decidedAt = $decidedAt;

        return $this;
    }

    public function getDecidedBy(): ?string
    {
        return $this->decidedBy;
    }

    public function setDecidedBy(?string $decidedBy): static
    {
        $this->decidedBy = $decidedBy;

        return $this;
    }

    public function getAppliedAt(): ?\DateTimeImmutable
    {
        return $this->appliedAt;
    }

    public function setAppliedAt(?\DateTimeImmutable $appliedAt): static
    {
        $this->appliedAt = $appliedAt;

        return $this;
    }

    public function getAppliedBy(): ?string
    {
        return $this->appliedBy;
    }

    public function setAppliedBy(?string $appliedBy): static
    {
        $this->appliedBy = $appliedBy;

        return $this;
    }

    public function getAppealDeadlineAt(): ?\DateTimeImmutable
    {
        return $this->appealDeadlineAt;
    }

    public function setAppealDeadlineAt(?\DateTimeImmutable $appealDeadlineAt): static
    {
        $this->appealDeadlineAt = $appealDeadlineAt;

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

    public function getClosedBy(): ?string
    {
        return $this->closedBy;
    }

    public function setClosedBy(?string $closedBy): static
    {
        $this->closedBy = $closedBy;

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

    public function getDocument(): ?Document
    {
        return $this->document;
    }

    public function setDocument(?Document $document): static
    {
        $this->document = $document;

        return $this;
    }

    public function getExitProcess(): ?ExitProcess
    {
        return $this->exitProcess;
    }

    public function setExitProcess(?ExitProcess $exitProcess): static
    {
        $this->exitProcess = $exitProcess;

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
