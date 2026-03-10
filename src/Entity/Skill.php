<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
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
use App\Model\SkillConstants;
use App\Repository\SkillRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SkillRepository::class)]
#[ORM\Table(name: '`skill`')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'skill:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_SKILL_DETAILS")',
            provider: ItemProvider::class
        ),
        new GetCollection(
            security: 'is_granted("ROLE_SKILL_LIST")',
            provider: CollectionProvider::class
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
    ]
)]
#[ApiFilter(\ApiPlatform\Doctrine\Orm\Filter\SearchFilter::class, properties: [
    'id' => 'exact',
    'employee' => 'exact',
    'name' => 'ipartial',
    'level' => 'exact',
])]
#[ApiFilter(\ApiPlatform\Doctrine\Orm\Filter\OrderFilter::class, properties: ['createdAt', 'updatedAt'])]
#[ApiFilter(\ApiPlatform\Doctrine\Orm\Filter\DateFilter::class, properties: ['createdAt', 'updatedAt'])]
class Skill implements RessourceInterface
{
    public const string ID_PREFIX = 'SK';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'SK_ID', length: 16)]
    #[Groups(['skill:get'])]
    private ?string $id = null;

    #[ORM\Column(name: 'SK_EMPLOYEE', length: 16)]
    #[Groups(['skill:get', 'skill:post'])]
    #[Assert\NotBlank]
    private ?string $employee = null;

    #[ORM\Column(name: 'SK_NAME', length: 120)]
    #[Groups(['skill:get', 'skill:post', 'skill:patch'])]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(name: 'SK_LEVEL', length: 15)]
    #[Groups(['skill:get', 'skill:post', 'skill:patch'])]
    #[Assert\Choice(callback: [SkillConstants::class, 'getLevels'])]
    #[Assert\NotBlank]
    private ?string $level = null;

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

    public function getEmployee(): ?string
    {
        return $this->employee;
    }

    public function setEmployee(string $employee): static
    {
        $this->employee = $employee;

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

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(string $level): static
    {
        $this->level = $level;

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
