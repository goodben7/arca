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
use App\Dto\CancelExitTaskDto;
use App\Dto\CompleteExitTaskDto;
use App\Dto\StartExitTaskDto;
use App\Model\ExitTaskConstants;
use App\Model\RessourceInterface;
use App\Repository\ExitTaskRepository;
use App\State\CancelExitTaskProcessor;
use App\State\CompleteExitTaskProcessor;
use App\State\StartExitTaskProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExitTaskRepository::class)]
#[ORM\Table(name: '`exit_task`')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'exit_task:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_EXIT_TASK_DETAILS")',
            provider: ItemProvider::class,
        ),
        new GetCollection(
            security: 'is_granted("ROLE_EXIT_TASK_LIST")',
            provider: CollectionProvider::class,
        ),
        new Post(
            uriTemplate: '/exit_tasks/starts',
            security: 'is_granted("ROLE_EXIT_TASK_START")',
            input: StartExitTaskDto::class,
            processor: StartExitTaskProcessor::class,
            status: 200,
        ),
        new Post(
            uriTemplate: '/exit_tasks/completions',
            security: 'is_granted("ROLE_EXIT_TASK_COMPLETE")',
            input: CompleteExitTaskDto::class,
            processor: CompleteExitTaskProcessor::class,
            status: 200,
        ),
        new Post(
            uriTemplate: '/exit_tasks/cancellations',
            security: 'is_granted("ROLE_EXIT_TASK_CANCEL")',
            input: CancelExitTaskDto::class,
            processor: CancelExitTaskProcessor::class,
            status: 200,
        ),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'process' => 'exact',
    'type' => 'exact',
    'status' => 'exact',
    'assignedTo' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['dueDate', 'createdAt', 'updatedAt'])]
#[ApiFilter(DateFilter::class, properties: ['dueDate', 'createdAt', 'updatedAt'])]
class ExitTask implements RessourceInterface
{
    public const string ID_PREFIX = 'XT';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'XT_ID', length: 16)]
    #[Groups(['exit_task:get'])]
    private ?string $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'XT_PROCESS', referencedColumnName: 'EP_ID', nullable: false)]
    #[Groups(['exit_task:get'])]
    #[Assert\NotNull]
    private ?ExitProcess $process = null;

    #[ORM\Column(name: 'XT_TITLE', length: 160)]
    #[Groups(['exit_task:get'])]
    #[Assert\NotBlank]
    private ?string $title = null;

    #[ORM\Column(name: 'XT_TYPE', length: 25)]
    #[Groups(['exit_task:get'])]
    #[Assert\Choice(callback: [ExitTaskConstants::class, 'getTypes'])]
    private ?string $type = null;

    #[ORM\Column(name: 'XT_STATUS', length: 15)]
    #[Groups(['exit_task:get'])]
    #[Assert\Choice(callback: [ExitTaskConstants::class, 'getStatuses'])]
    private ?string $status = null;

    #[ORM\Column(name: 'XT_ASSIGNED_TO', length: 16, nullable: true)]
    #[Groups(['exit_task:get'])]
    private ?string $assignedTo = null;

    #[ORM\Column(name: 'XT_DUE_DATE', type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['exit_task:get'])]
    private ?\DateTimeImmutable $dueDate = null;

    #[ORM\Column(name: 'XT_CREATED_AT')]
    #[Groups(['exit_task:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'XT_UPDATED_AT', nullable: true)]
    #[Groups(['exit_task:get'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getProcess(): ?ExitProcess
    {
        return $this->process;
    }

    public function setProcess(ExitProcess $process): static
    {
        $this->process = $process;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

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

    public function getAssignedTo(): ?string
    {
        return $this->assignedTo;
    }

    public function setAssignedTo(?string $assignedTo): static
    {
        $this->assignedTo = $assignedTo;

        return $this;
    }

    public function getDueDate(): ?\DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTimeImmutable $dueDate): static
    {
        $this->dueDate = $dueDate;

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
