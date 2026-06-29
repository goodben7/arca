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
use App\Repository\SkillCategoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SkillCategoryRepository::class)]
#[ORM\Table(name: '`skill_category`')]
#[ORM\UniqueConstraint(name: 'UNIQ_SKILL_CATEGORY_CODE', fields: ['code'])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'skill_category:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_SKILL_CATEGORY_DETAILS")',
            provider: ItemProvider::class,
        ),
        new GetCollection(
            security: 'is_granted("ROLE_SKILL_CATEGORY_LIST")',
            provider: CollectionProvider::class,
        ),
        new Post(
            security: 'is_granted("ROLE_SKILL_CATEGORY_CREATE")',
            denormalizationContext: ['groups' => 'skill_category:post'],
            processor: PersistProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_SKILL_CATEGORY_UPDATE")',
            denormalizationContext: ['groups' => 'skill_category:patch'],
            processor: PersistProcessor::class,
        ),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'code' => 'ipartial',
    'name' => 'ipartial',
])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'updatedAt', 'name', 'code'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'updatedAt'])]
class SkillCategory implements RessourceInterface
{
    public const string ID_PREFIX = 'SKC';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'SKC_ID', length: 16)]
    #[Groups(['skill_category:get', 'skill:get'])]
    private ?string $id = null;

    #[ORM\Column(name: 'SKC_CODE', length: 40)]
    #[Groups(['skill_category:get', 'skill_category:post', 'skill_category:patch', 'skill:get'])]
    #[Assert\NotBlank]
    private ?string $code = null;

    #[ORM\Column(name: 'SKC_NAME', length: 120)]
    #[Groups(['skill_category:get', 'skill_category:post', 'skill_category:patch', 'skill:get'])]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(name: 'SKC_DESCRIPTION', type: Types::TEXT, nullable: true)]
    #[Groups(['skill_category:get', 'skill_category:post', 'skill_category:patch', 'skill:get'])]
    private ?string $description = null;

    #[ORM\Column(name: 'SKC_CREATED_AT')]
    #[Groups(['skill_category:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'SKC_UPDATED_AT', nullable: true)]
    #[Groups(['skill_category:get'])]
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
