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
use App\Dto\CancelExitProcessDto;
use App\Dto\CompleteExitProcessDto;
use App\Dto\CreateExitProcessDto;
use App\Dto\StartExitProcessDto;
use App\Model\ExitProcessConstants;
use App\Model\RessourceInterface;
use App\Repository\ExitProcessRepository;
use App\State\CancelExitProcessProcessor;
use App\State\CompleteExitProcessProcessor;
use App\State\CreateExitProcessProcessor;
use App\State\StartExitProcessProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExitProcessRepository::class)]
#[ORM\Table(name: '`exit_process`')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'exit_process:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_EXIT_PROCESS_DETAILS")',
            provider: ItemProvider::class,
        ),
        new GetCollection(
            security: 'is_granted("ROLE_EXIT_PROCESS_LIST")',
            provider: CollectionProvider::class,
        ),
        new Post(
            security: 'is_granted("ROLE_EXIT_PROCESS_CREATE")',
            input: CreateExitProcessDto::class,
            processor: CreateExitProcessProcessor::class,
        ),
        new Post(
            uriTemplate: '/exit_processes/starts',
            security: 'is_granted("ROLE_EXIT_PROCESS_START")',
            input: StartExitProcessDto::class,
            processor: StartExitProcessProcessor::class,
            status: 200,
        ),
        new Post(
            uriTemplate: '/exit_processes/completions',
            security: 'is_granted("ROLE_EXIT_PROCESS_COMPLETE")',
            input: CompleteExitProcessDto::class,
            processor: CompleteExitProcessProcessor::class,
            status: 200,
        ),
        new Post(
            uriTemplate: '/exit_processes/cancellations',
            security: 'is_granted("ROLE_EXIT_PROCESS_CANCEL")',
            input: CancelExitProcessDto::class,
            processor: CancelExitProcessProcessor::class,
            status: 200,
        ),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'employee' => 'exact',
    'status' => 'exact',
    'reason' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['departureDate', 'startedAt', 'completedAt', 'createdAt'])]
#[ApiFilter(DateFilter::class, properties: ['departureDate', 'startedAt', 'completedAt', 'createdAt'])]
class ExitProcess implements RessourceInterface
{
    public const string ID_PREFIX = 'EP';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'EP_ID', length: 16)]
    #[Groups(['exit_process:get', 'exit_task:get', 'disciplinary_case:get'])]
    private ?string $id = null;

    #[ORM\Column(name: 'EP_EMPLOYEE', length: 16)]
    #[Groups(['exit_process:get'])]
    #[Assert\NotBlank]
    private ?string $employee = null;

    #[ORM\Column(name: 'EP_REASON', length: 20)]
    #[Groups(['exit_process:get', 'disciplinary_case:get'])]
    #[Assert\Choice(callback: [ExitProcessConstants::class, 'getReasons'])]
    #[Assert\NotBlank]
    private ?string $reason = null;

    #[ORM\Column(name: 'EP_DEPARTURE_DATE', type: Types::DATE_IMMUTABLE)]
    #[Groups(['exit_process:get'])]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $departureDate = null;

    #[ORM\Column(name: 'EP_STATUS', length: 15)]
    #[Groups(['exit_process:get', 'disciplinary_case:get'])]
    #[Assert\Choice(callback: [ExitProcessConstants::class, 'getStatuses'])]
    private ?string $status = null;

    #[ORM\Column(name: 'EP_STARTED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['exit_process:get'])]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(name: 'EP_COMPLETED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['exit_process:get'])]
    private ?\DateTimeImmutable $completedAt = null;

    #[ORM\Column(name: 'EP_CREATED_AT')]
    #[Groups(['exit_process:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'EP_UPDATED_AT', nullable: true)]
    #[Groups(['exit_process:get'])]
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

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(string $reason): static
    {
        $this->reason = $reason;

        return $this;
    }

    public function getDepartureDate(): ?\DateTimeImmutable
    {
        return $this->departureDate;
    }

    public function setDepartureDate(\DateTimeImmutable $departureDate): static
    {
        $this->departureDate = $departureDate;

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

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(?\DateTimeImmutable $startedAt): static
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeImmutable $completedAt): static
    {
        $this->completedAt = $completedAt;

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
