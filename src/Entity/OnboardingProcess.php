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
use App\Dto\CancelOnboardingProcessDto;
use App\Dto\CompleteOnboardingProcessDto;
use App\Model\OnboardingProcessConstants;
use App\Model\RessourceInterface;
use App\Repository\OnboardingProcessRepository;
use App\State\CancelOnboardingProcessProcessor;
use App\State\CompleteOnboardingProcessProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OnboardingProcessRepository::class)]
#[ORM\Table(name: '`onboarding_process`')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'onboarding_process:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_ONBOARDING_PROCESS_DETAILS")',
            provider: ItemProvider::class,
        ),
        new GetCollection(
            security: 'is_granted("ROLE_ONBOARDING_PROCESS_LIST")',
            provider: CollectionProvider::class,
        ),
        new Post(
            uriTemplate: '/onboarding_processes/completions',
            security: 'is_granted("ROLE_ONBOARDING_PROCESS_COMPLETE")',
            input: CompleteOnboardingProcessDto::class,
            processor: CompleteOnboardingProcessProcessor::class,
            status: 200,
        ),
        new Post(
            uriTemplate: '/onboarding_processes/cancellations',
            security: 'is_granted("ROLE_ONBOARDING_PROCESS_CANCEL")',
            input: CancelOnboardingProcessDto::class,
            processor: CancelOnboardingProcessProcessor::class,
            status: 200,
        ),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'employee' => 'exact',
    'status' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['startedAt', 'completedAt', 'createdAt'])]
#[ApiFilter(DateFilter::class, properties: ['startedAt', 'completedAt', 'createdAt'])]
class OnboardingProcess implements RessourceInterface
{
    public const string ID_PREFIX = 'OP';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'OP_ID', length: 16)]
    #[Groups(['onboarding_process:get', 'onboarding_task:get'])]
    private ?string $id = null;

    #[ORM\Column(name: 'OP_EMPLOYEE', length: 16)]
    #[Groups(['onboarding_process:get'])]
    #[Assert\NotBlank]
    private ?string $employee = null;

    #[ORM\Column(name: 'OP_STATUS', length: 15)]
    #[Groups(['onboarding_process:get'])]
    #[Assert\Choice(callback: [OnboardingProcessConstants::class, 'getStatuses'])]
    private ?string $status = null;

    #[ORM\Column(name: 'OP_STARTED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['onboarding_process:get'])]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(name: 'OP_COMPLETED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['onboarding_process:get'])]
    private ?\DateTimeImmutable $completedAt = null;

    #[ORM\Column(name: 'OP_CREATED_AT')]
    #[Groups(['onboarding_process:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'OP_UPDATED_AT', nullable: true)]
    #[Groups(['onboarding_process:get'])]
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
