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
use App\Dto\ApproveLeaveRequestDto;
use App\Dto\RejectLeaveRequestDto;
use App\Model\LeaveRequestConstants;
use App\Model\RessourceInterface;
use App\Repository\LeaveRequestRepository;
use App\State\ApproveLeaveRequestProcessor;
use App\State\RejectLeaveRequestProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LeaveRequestRepository::class)]
#[ORM\Table(name: '`leave_request`')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'leave_request:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_LEAVE_REQUEST_DETAILS")',
            provider: ItemProvider::class
        ),
        new GetCollection(
            security: 'is_granted("ROLE_LEAVE_REQUEST_LIST")',
            provider: CollectionProvider::class
        ),
        new Post(
            security: 'is_granted("ROLE_LEAVE_REQUEST_CREATE")',
            denormalizationContext: ['groups' => 'leave_request:post'],
            processor: PersistProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_LEAVE_REQUEST_UPDATE")',
            denormalizationContext: ['groups' => 'leave_request:patch'],
            processor: PersistProcessor::class,
        ),
        new Post(
            uriTemplate: '/leave_requests/approvals',
            security: 'is_granted("ROLE_LEAVE_REQUEST_APPROVE")',
            input: ApproveLeaveRequestDto::class,
            processor: ApproveLeaveRequestProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/leave_requests/rejections',
            security: 'is_granted("ROLE_LEAVE_REQUEST_REJECT")',
            input: RejectLeaveRequestDto::class,
            processor: RejectLeaveRequestProcessor::class,
            status: 200
        ),
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'employee' => 'exact',
    'type' => 'exact',
    'status' => 'exact',
    'approvedBy' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'updatedAt', 'startDate', 'endDate'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'updatedAt', 'startDate', 'endDate'])]
class LeaveRequest implements RessourceInterface
{
    public const string ID_PREFIX = 'LR';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'LR_ID', length: 16)]
    #[Groups(['leave_request:get'])]
    private ?string $id = null;

    #[ORM\Column(name: 'LR_EMPLOYEE', length: 16)]
    #[Groups(['leave_request:get', 'leave_request:post', 'leave_request:patch'])]
    #[Assert\NotBlank]
    private ?string $employee = null;

    #[ORM\Column(name: 'LR_TYPE', length: 10)]
    #[Groups(['leave_request:get', 'leave_request:post', 'leave_request:patch'])]
    #[Assert\Choice(callback: [LeaveRequestConstants::class, 'getTypes'])]
    #[Assert\NotBlank]
    private ?string $type = null;

    #[ORM\Column(name: 'LR_START_DATE', type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['leave_request:get', 'leave_request:post', 'leave_request:patch'])]
    #[Assert\NotNull]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(name: 'LR_END_DATE', type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['leave_request:get', 'leave_request:post', 'leave_request:patch'])]
    #[Assert\NotNull]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(name: 'LR_NUMBER_OF_DAYS')]
    #[Groups(['leave_request:get', 'leave_request:post', 'leave_request:patch'])]
    #[Assert\Positive]
    #[Assert\NotNull]
    private ?int $numberOfDays = null;

    #[ORM\Column(name: 'LR_STATUS', length: 15)]
    #[Groups(['leave_request:get', 'leave_request:post', 'leave_request:patch'])]
    #[Assert\Choice(callback: [LeaveRequestConstants::class, 'getStatuses'])]
    #[Assert\NotBlank]
    private ?string $status = null;

    #[ORM\Column(name: 'LR_REASON', type: Types::TEXT, nullable: true)]
    #[Groups(['leave_request:get', 'leave_request:post', 'leave_request:patch'])]
    private ?string $reason = null;

    #[ORM\Column(name: 'LR_APPROVED_BY', length: 16, nullable: true)]
    #[Groups(['leave_request:get', 'leave_request:post', 'leave_request:patch'])]
    private ?string $approvedBy = null;

    #[ORM\Column(name: 'LR_CREATED_AT')]
    #[Groups(['leave_request:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'LR_UPDATED_AT', nullable: true)]
    #[Groups(['leave_request:get'])]
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

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getNumberOfDays(): ?int
    {
        return $this->numberOfDays;
    }

    public function setNumberOfDays(int $numberOfDays): static
    {
        $this->numberOfDays = $numberOfDays;

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

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): static
    {
        $this->reason = $reason;

        return $this;
    }

    public function getApprovedBy(): ?string
    {
        return $this->approvedBy;
    }

    public function setApprovedBy(?string $approvedBy): static
    {
        $this->approvedBy = $approvedBy;

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
