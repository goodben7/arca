<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
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
use App\Model\RessourceInterface;
use App\Model\SanctionScaleConstants;
use App\Repository\SanctionScaleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SanctionScaleRepository::class)]
#[ORM\Table(name: '`sanction_scale`')]
#[ORM\UniqueConstraint(name: 'UNIQ_SANCTION_SCALE_CODE', fields: ['code'])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'sanction_scale:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_SANCTION_SCALE_DETAILS")',
            provider: ItemProvider::class,
        ),
        new GetCollection(
            security: 'is_granted("ROLE_SANCTION_SCALE_LIST")',
            provider: CollectionProvider::class,
        ),
        new Post(
            security: 'is_granted("ROLE_SANCTION_SCALE_CREATE")',
            denormalizationContext: ['groups' => 'sanction_scale:post'],
            processor: PersistProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_SANCTION_SCALE_UPDATE")',
            denormalizationContext: ['groups' => 'sanction_scale:patch'],
            processor: PersistProcessor::class,
        ),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'code' => 'exact',
    'label' => 'ipartial',
    'severityLevel' => 'exact',
])]
#[ApiFilter(BooleanFilter::class, properties: ['active', 'requiresHearing'])]
#[ApiFilter(OrderFilter::class, properties: ['severityLevel', 'code', 'label', 'createdAt'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'updatedAt'])]
class SanctionScale implements RessourceInterface
{
    public const string ID_PREFIX = 'SS';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'SS_ID', length: 16)]
    #[Groups(['sanction_scale:get', 'disciplinary_case:get'])]
    private ?string $id = null;

    #[ORM\Column(name: 'SS_CODE', length: 40)]
    #[Groups(['sanction_scale:get', 'sanction_scale:post', 'sanction_scale:patch', 'disciplinary_case:get'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 40)]
    private ?string $code = null;

    #[ORM\Column(name: 'SS_LABEL', length: 120)]
    #[Groups(['sanction_scale:get', 'sanction_scale:post', 'sanction_scale:patch', 'disciplinary_case:get'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    private ?string $label = null;

    #[ORM\Column(name: 'SS_SEVERITY_LEVEL')]
    #[Groups(['sanction_scale:get', 'sanction_scale:post', 'sanction_scale:patch', 'disciplinary_case:get'])]
    #[Assert\NotNull]
    #[Assert\Range(min: SanctionScaleConstants::SEVERITY_MIN, max: SanctionScaleConstants::SEVERITY_MAX)]
    private ?int $severityLevel = null;

    #[ORM\Column(name: 'SS_REQUIRES_HEARING')]
    #[Groups(['sanction_scale:get', 'sanction_scale:post', 'sanction_scale:patch', 'disciplinary_case:get'])]
    #[Assert\NotNull]
    private bool $requiresHearing = false;

    #[ORM\Column(name: 'SS_MAX_DURATION_DAYS', nullable: true)]
    #[Groups(['sanction_scale:get', 'sanction_scale:post', 'sanction_scale:patch'])]
    #[Assert\Positive]
    private ?int $maxDurationDays = null;

    #[ORM\Column(name: 'SS_ACTIVE')]
    #[Groups(['sanction_scale:get', 'sanction_scale:post', 'sanction_scale:patch'])]
    #[Assert\NotNull]
    private bool $active = true;

    #[ORM\Column(name: 'SS_CREATED_AT')]
    #[Groups(['sanction_scale:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'SS_UPDATED_AT', nullable: true)]
    #[Groups(['sanction_scale:get'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getSeverityLevel(): ?int
    {
        return $this->severityLevel;
    }

    public function setSeverityLevel(int $severityLevel): static
    {
        $this->severityLevel = $severityLevel;

        return $this;
    }

    public function isRequiresHearing(): bool
    {
        return $this->requiresHearing;
    }

    public function setRequiresHearing(bool $requiresHearing): static
    {
        $this->requiresHearing = $requiresHearing;

        return $this;
    }

    public function getMaxDurationDays(): ?int
    {
        return $this->maxDurationDays;
    }

    public function setMaxDurationDays(?int $maxDurationDays): static
    {
        $this->maxDurationDays = $maxDurationDays;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

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
