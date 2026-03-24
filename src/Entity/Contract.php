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
use App\Dto\ActivateContractDto;
use App\Dto\CancelContractDto;
use App\Dto\EndContractDto;
use App\Dto\SetContractPendingDto;
use App\Model\ContractConstants;
use App\Model\RessourceInterface;
use App\Repository\ContractRepository;
use App\State\ActivateContractProcessor;
use App\State\CancelContractProcessor;
use App\State\EndContractProcessor;
use App\State\SetContractPendingProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContractRepository::class)]
#[ORM\Table(name: '`contract`')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'contract:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_CONTRACT_DETAILS")',
            provider: ItemProvider::class
        ),
        new GetCollection(
            security: 'is_granted("ROLE_CONTRACT_LIST")',
            provider: CollectionProvider::class
        ),
        new Post(
            security: 'is_granted("ROLE_CONTRACT_CREATE")',
            denormalizationContext: ['groups' => 'contract:post'],
            processor: PersistProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_CONTRACT_UPDATE")',
            denormalizationContext: ['groups' => 'contract:patch'],
            processor: PersistProcessor::class,
        ),
        new Post(
            uriTemplate: '/contracts/activations',
            security: 'is_granted("ROLE_CONTRACT_ACTIVATE")',
            input: ActivateContractDto::class,
            processor: ActivateContractProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/contracts/endings',
            security: 'is_granted("ROLE_CONTRACT_END")',
            input: EndContractDto::class,
            processor: EndContractProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/contracts/cancellations',
            security: 'is_granted("ROLE_CONTRACT_CANCEL")',
            input: CancelContractDto::class,
            processor: CancelContractProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/contracts/pendings',
            security: 'is_granted("ROLE_CONTRACT_SET_PENDING")',
            input: SetContractPendingDto::class,
            processor: SetContractPendingProcessor::class,
            status: 200
        ),
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'employee' => 'exact',
    'type' => 'exact',
    'status' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'updatedAt'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'updatedAt', 'startDate', 'endDate'])]
class Contract implements RessourceInterface
{
    public const string ID_PREFIX = 'CT';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'CT_ID', length: 16)]
    #[Groups(['contract:get'])]
    private ?string $id = null;

    #[ORM\Column(name: 'CT_EMPLOYEE', length: 16)]
    #[Groups(['contract:get', 'contract:post', 'contract:patch'])]
    #[Assert\NotBlank]
    private ?string $employee = null;

    #[ORM\Column(name: 'CT_TYPE', length: 40)]
    #[Groups(['contract:get', 'contract:post', 'contract:patch'])]
    #[Assert\Choice(callback: [ContractConstants::class, 'getTypes'])]
    #[Assert\NotBlank]
    private ?string $type = null;

    #[ORM\Column(name: 'CT_START_DATE', type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['contract:get', 'contract:post', 'contract:patch'])]
    #[Assert\NotNull]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(name: 'CT_END_DATE', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['contract:get', 'contract:post', 'contract:patch'])]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(name: 'CT_SALARY', type: Types::DECIMAL, precision: 14, scale: 2)]
    #[Groups(['contract:get', 'contract:post', 'contract:patch'])]
    #[Assert\NotBlank]
    private ?string $salary = null;

    #[ORM\Column(name: 'CT_STATUS', length: 15)]
    #[Groups(['contract:get', 'contract:post', 'contract:patch'])]
    #[Assert\Choice(callback: [ContractConstants::class, 'getStatuses'])]
    #[Assert\NotBlank]
    private ?string $status = null;

    #[ORM\Column(name: 'CT_ACTIVATED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['contract:get'])]
    private ?\DateTimeImmutable $activatedAt = null;

    #[ORM\Column(name: 'CT_ACTIVATED_BY', length: 16, nullable: true)]
    #[Groups(['contract:get'])]
    private ?string $activatedBy = null;

    #[ORM\Column(name: 'CT_ENDED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['contract:get'])]
    private ?\DateTimeImmutable $endedAt = null;

    #[ORM\Column(name: 'CT_ENDED_BY', length: 16, nullable: true)]
    #[Groups(['contract:get'])]
    private ?string $endedBy = null;

    #[ORM\Column(name: 'CT_CANCELLED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['contract:get'])]
    private ?\DateTimeImmutable $cancelledAt = null;

    #[ORM\Column(name: 'CT_CANCELLED_BY', length: 16, nullable: true)]
    #[Groups(['contract:get'])]
    private ?string $cancelledBy = null;

    #[ORM\Column(name: 'CT_PENDING_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['contract:get'])]
    private ?\DateTimeImmutable $pendingAt = null;

    #[ORM\Column(name: 'CT_PENDING_BY', length: 16, nullable: true)]
    #[Groups(['contract:get'])]
    private ?string $pendingBy = null;

    #[ORM\Column(name: 'CT_CREATED_AT')]
    #[Groups(['contract:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'CT_UPDATED_AT', nullable: true)]
    #[Groups(['contract:get'])]
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

    public function setEndDate(?\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getSalary(): ?string
    {
        return $this->salary;
    }

    public function setSalary(string $salary): static
    {
        $this->salary = $salary;

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

    public function getActivatedAt(): ?\DateTimeImmutable
    {
        return $this->activatedAt;
    }

    public function setActivatedAt(?\DateTimeImmutable $activatedAt): static
    {
        $this->activatedAt = $activatedAt;

        return $this;
    }

    public function getActivatedBy(): ?string
    {
        return $this->activatedBy;
    }

    public function setActivatedBy(?string $activatedBy): static
    {
        $this->activatedBy = $activatedBy;

        return $this;
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTimeImmutable $endedAt): static
    {
        $this->endedAt = $endedAt;

        return $this;
    }

    public function getEndedBy(): ?string
    {
        return $this->endedBy;
    }

    public function setEndedBy(?string $endedBy): static
    {
        $this->endedBy = $endedBy;

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

    public function getPendingAt(): ?\DateTimeImmutable
    {
        return $this->pendingAt;
    }

    public function setPendingAt(?\DateTimeImmutable $pendingAt): static
    {
        $this->pendingAt = $pendingAt;

        return $this;
    }

    public function getPendingBy(): ?string
    {
        return $this->pendingBy;
    }

    public function setPendingBy(?string $pendingBy): static
    {
        $this->pendingBy = $pendingBy;

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
