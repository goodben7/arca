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
use App\Model\RessourceInterface;
use App\Repository\SkillRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Compétence du catalogue référentiel RH.
 *
 * Les compétences détenues par un employé sont modélisées via `EmployeeSkill`.
 */
#[ORM\Entity(repositoryClass: SkillRepository::class)]
#[ORM\Table(name: '`skill`')]
#[ORM\UniqueConstraint(name: 'UNIQ_SKILL_CODE', fields: ['code'])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'skill:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_SKILL_DETAILS")',
            provider: ItemProvider::class,
        ),
        new GetCollection(
            security: 'is_granted("ROLE_SKILL_LIST")',
            provider: CollectionProvider::class,
        ),
        new Post(
            security: 'is_granted("ROLE_SKILL_CREATE")',
            denormalizationContext: ['groups' => 'skill:post'],
            processor: PersistProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_SKILL_UPDATE")',
            denormalizationContext: ['groups' => 'skill:patch'],
            processor: PersistProcessor::class,
        ),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'code' => 'ipartial',
    'name' => 'ipartial',
    'category' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'updatedAt', 'name', 'code'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'updatedAt'])]
class Skill implements RessourceInterface
{
    public const string ID_PREFIX = 'SK';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'SK_ID', length: 16)]
    #[Groups(['skill:get', 'employee_skill:get'])]
    private ?string $id = null;

    #[ORM\Column(name: 'SK_CODE', length: 40)]
    #[Groups(['skill:get', 'skill:post', 'skill:patch', 'employee_skill:get'])]
    #[Assert\NotBlank]
    private ?string $code = null;

    #[ORM\Column(name: 'SK_NAME', length: 120)]
    #[Groups(['skill:get', 'skill:post', 'skill:patch', 'employee_skill:get'])]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'SK_CATEGORY', referencedColumnName: 'SKC_ID', nullable: false)]
    #[Groups(['skill:get', 'skill:post', 'skill:patch'])]
    #[Assert\NotNull]
    private ?SkillCategory $category = null;

    #[ORM\Column(name: 'SK_DESCRIPTION', type: Types::TEXT, nullable: true)]
    #[Groups(['skill:get', 'skill:post', 'skill:patch', 'employee_skill:get'])]
    private ?string $description = null;

    #[ORM\Column(name: 'SK_CREATED_AT')]
    #[Groups(['skill:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'SK_UPDATED_AT', nullable: true)]
    #[Groups(['skill:get'])]
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCategory(): ?SkillCategory
    {
        return $this->category;
    }

    public function setCategory(SkillCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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
