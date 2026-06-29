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
use App\Dto\RecordCompensationHistoryDto;
use App\Model\CompensationHistoryConstants;
use App\Model\RessourceInterface;
use App\Repository\CompensationHistoryRepository;
use App\State\RecordCompensationHistoryProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CompensationHistoryRepository::class)]
#[ORM\Table(name: '`compensation_history`')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'compensation_history:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_COMPENSATION_HISTORY_DETAILS")',
            provider: ItemProvider::class,
        ),
        new GetCollection(
            security: 'is_granted("ROLE_COMPENSATION_HISTORY_LIST")',
            provider: CollectionProvider::class,
        ),
        new Post(
            uriTemplate: '/compensation_histories/recordings',
            security: 'is_granted("ROLE_COMPENSATION_HISTORY_RECORD")',
            input: RecordCompensationHistoryDto::class,
            processor: RecordCompensationHistoryProcessor::class,
            status: 200,
        ),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'employee' => 'exact',
    'sourceEvent' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['effectiveDate', 'createdAt'])]
#[ApiFilter(DateFilter::class, properties: ['effectiveDate', 'createdAt'])]
class CompensationHistory implements RessourceInterface
{
    public const string ID_PREFIX = 'CH';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'CH_ID', length: 16)]
    #[Groups(['compensation_history:get'])]
    private ?string $id = null;

    #[ORM\Column(name: 'CH_EMPLOYEE', length: 16)]
    #[Groups(['compensation_history:get'])]
    #[Assert\NotBlank]
    private ?string $employee = null;

    #[ORM\Column(name: 'CH_OLD_SALARY', type: Types::DECIMAL, precision: 14, scale: 2)]
    #[Groups(['compensation_history:get'])]
    #[Assert\NotBlank]
    private ?string $oldSalary = null;

    #[ORM\Column(name: 'CH_NEW_SALARY', type: Types::DECIMAL, precision: 14, scale: 2)]
    #[Groups(['compensation_history:get'])]
    #[Assert\NotBlank]
    private ?string $newSalary = null;

    #[ORM\Column(name: 'CH_EFFECTIVE_DATE', type: Types::DATE_IMMUTABLE)]
    #[Groups(['compensation_history:get'])]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $effectiveDate = null;

    #[ORM\Column(name: 'CH_REASON', type: Types::TEXT)]
    #[Groups(['compensation_history:get'])]
    #[Assert\NotBlank]
    private ?string $reason = null;

    #[ORM\Column(name: 'CH_SOURCE_EVENT', length: 30)]
    #[Groups(['compensation_history:get'])]
    #[Assert\Choice(callback: [CompensationHistoryConstants::class, 'getSourceEvents'])]
    #[Assert\NotBlank]
    private ?string $sourceEvent = null;

    #[ORM\Column(name: 'CH_CREATED_AT')]
    #[Groups(['compensation_history:get'])]
    private ?\DateTimeImmutable $createdAt = null;

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

    public function getOldSalary(): ?string
    {
        return $this->oldSalary;
    }

    public function setOldSalary(string $oldSalary): static
    {
        $this->oldSalary = $oldSalary;

        return $this;
    }

    public function getNewSalary(): ?string
    {
        return $this->newSalary;
    }

    public function setNewSalary(string $newSalary): static
    {
        $this->newSalary = $newSalary;

        return $this;
    }

    public function getEffectiveDate(): ?\DateTimeImmutable
    {
        return $this->effectiveDate;
    }

    public function setEffectiveDate(\DateTimeImmutable $effectiveDate): static
    {
        $this->effectiveDate = $effectiveDate;

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

    public function getSourceEvent(): ?string
    {
        return $this->sourceEvent;
    }

    public function setSourceEvent(string $sourceEvent): static
    {
        $this->sourceEvent = $sourceEvent;

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

    #[ORM\PrePersist]
    public function buildCreatedAt(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }
}
