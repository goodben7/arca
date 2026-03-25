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
use App\Doctrine\IdGenerator;
use App\Dto\CancelTrainingSessionDto;
use App\Dto\CreateTrainingSessionDto;
use App\Dto\CompleteTrainingSessionDto;
use App\Dto\SetTrainingSessionPlannedDto;
use App\Dto\StartTrainingSessionDto;
use App\Model\RessourceInterface;
use App\Model\TrainingSessionConstants;
use App\Repository\TrainingSessionRepository;
use App\State\CreateTrainingSessionProcessor;
use App\State\CancelTrainingSessionProcessor;
use App\State\CompleteTrainingSessionProcessor;
use App\State\SetTrainingSessionPlannedProcessor;
use App\State\StartTrainingSessionProcessor;
use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TrainingSessionRepository::class)]
#[ORM\Table(name: '`training_session`')]
#[ApiResource(
    normalizationContext: ['groups' => 'training_session:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_TRAINING_SESSION_DETAILS")',
            provider: ItemProvider::class
        ),
        new GetCollection(
            security: 'is_granted("ROLE_TRAINING_SESSION_LIST")',
            provider: CollectionProvider::class
        ),
        new Post(
            security: 'is_granted("ROLE_TRAINING_SESSION_CREATE")',
            input: CreateTrainingSessionDto::class,
            processor: CreateTrainingSessionProcessor::class 
        ),
        new Patch(
            security: 'is_granted("ROLE_TRAINING_SESSION_UPDATE")',
            denormalizationContext: ['groups' => 'training_session:patch',],
            processor: PersistProcessor::class,
        ),
        new Post(
            uriTemplate: '/training_sessions/startings',
            security: 'is_granted("ROLE_TRAINING_SESSION_START")',
            input: StartTrainingSessionDto::class,
            processor: StartTrainingSessionProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/training_sessions/completions',
            security: 'is_granted("ROLE_TRAINING_SESSION_COMPLETE")',
            input: CompleteTrainingSessionDto::class,
            processor: CompleteTrainingSessionProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/training_sessions/cancellations',
            security: 'is_granted("ROLE_TRAINING_SESSION_CANCEL")',
            input: CancelTrainingSessionDto::class,
            processor: CancelTrainingSessionProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/training_sessions/plannings',
            security: 'is_granted("ROLE_TRAINING_SESSION_SET_PLANNED")',
            input: SetTrainingSessionPlannedDto::class,
            processor: SetTrainingSessionPlannedProcessor::class,
            status: 200
        ),
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'trainingRequest' => 'exact',
    'status' => 'exact',
    'trainer' => 'ipartial',
    'location' => 'ipartial',
])]
#[ApiFilter(OrderFilter::class, properties: ['startDate', 'endDate'])]
class TrainingSession implements RessourceInterface
{
    public const string ID_PREFIX = 'TS';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'TS_ID', length: 16)]
    #[Groups(['training_session:get'])]
    private ?string $id = null;

    #[ORM\Column(name: 'TS_TITLE', length: 160)]
    #[Groups(['training_session:get', 'training_session:patch'])]
    #[Assert\NotBlank]
    private ?string $title = null;

    #[ORM\Column(name: 'TS_TRAINER', length: 160)]
    #[Groups(['training_session:get', 'training_session:patch'])]
    #[Assert\NotBlank]
    private ?string $trainer = null;

    #[ORM\Column(name: 'TS_START_DATE', type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['training_session:get', 'training_session:patch'])]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(name: 'TS_END_DATE', type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['training_session:get', 'training_session:patch'])]
    #[Assert\NotNull]
    #[Assert\GreaterThan(propertyPath: 'startDate')]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\Column(name: 'TS_LOCATION', length: 160)]
    #[Groups(['training_session:get', 'training_session:patch'])]
    #[Assert\NotBlank]
    private ?string $location = null;

    #[ORM\Column(name: 'TS_CAPACITY')]
    #[Groups(['training_session:get', 'training_session:patch'])]
    #[Assert\Positive]
    #[Assert\NotNull]
    private ?int $capacity = null;

    #[ORM\Column(name: 'TS_TRAINING_REQUEST', length: 16)]
    #[Groups(['training_session:get'])]
    #[Assert\NotBlank]
    private ?string $trainingRequest = null;

    #[ORM\Column(name: 'TS_STATUS', length: 15)]
    #[Groups(['training_session:get'])]
    #[Assert\Choice(callback: [TrainingSessionConstants::class, 'getStatuses'])]
    #[Assert\NotBlank]
    private ?string $status = null;

    #[ORM\Column(name: 'TS_STARTED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['training_session:get'])]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(name: 'TS_STARTED_BY', length: 16, nullable: true)]
    #[Groups(['training_session:get'])]
    private ?string $startedBy = null;

    #[ORM\Column(name: 'TS_COMPLETED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['training_session:get'])]
    private ?\DateTimeImmutable $completedAt = null;

    #[ORM\Column(name: 'TS_COMPLETED_BY', length: 16, nullable: true)]
    #[Groups(['training_session:get'])]
    private ?string $completedBy = null;

    #[ORM\Column(name: 'TS_CANCELLED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['training_session:get'])]
    private ?\DateTimeImmutable $cancelledAt = null;

    #[ORM\Column(name: 'TS_CANCELLED_BY', length: 16, nullable: true)]
    #[Groups(['training_session:get'])]
    private ?string $cancelledBy = null;

    public function getId(): ?string { return $this->id; }
    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }
    public function getTrainer(): ?string { return $this->trainer; }
    public function setTrainer(string $trainer): static { $this->trainer = $trainer; return $this; }
    public function getStartDate(): ?\DateTimeImmutable { return $this->startDate; }
    public function setStartDate(\DateTimeImmutable $startDate): static { $this->startDate = $startDate; return $this; }
    public function getEndDate(): ?\DateTimeImmutable { return $this->endDate; }
    public function setEndDate(\DateTimeImmutable $endDate): static { $this->endDate = $endDate; return $this; }
    public function getLocation(): ?string { return $this->location; }
    public function setLocation(string $location): static { $this->location = $location; return $this; }
    public function getCapacity(): ?int { return $this->capacity; }
    public function setCapacity(int $capacity): static { $this->capacity = $capacity; return $this; }
    public function getTrainingRequest(): ?string { return $this->trainingRequest; }
    public function setTrainingRequest(string $trainingRequest): static { $this->trainingRequest = $trainingRequest; return $this; }
    public function getStatus(): ?string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }
    public function getStartedAt(): ?\DateTimeImmutable { return $this->startedAt; }
    public function setStartedAt(?\DateTimeImmutable $startedAt): static { $this->startedAt = $startedAt; return $this; }
    public function getStartedBy(): ?string { return $this->startedBy; }
    public function setStartedBy(?string $startedBy): static { $this->startedBy = $startedBy; return $this; }
    public function getCompletedAt(): ?\DateTimeImmutable { return $this->completedAt; }
    public function setCompletedAt(?\DateTimeImmutable $completedAt): static { $this->completedAt = $completedAt; return $this; }
    public function getCompletedBy(): ?string { return $this->completedBy; }
    public function setCompletedBy(?string $completedBy): static { $this->completedBy = $completedBy; return $this; }
    public function getCancelledAt(): ?\DateTimeImmutable { return $this->cancelledAt; }
    public function setCancelledAt(?\DateTimeImmutable $cancelledAt): static { $this->cancelledAt = $cancelledAt; return $this; }
    public function getCancelledBy(): ?string { return $this->cancelledBy; }
    public function setCancelledBy(?string $cancelledBy): static { $this->cancelledBy = $cancelledBy; return $this; }
}
