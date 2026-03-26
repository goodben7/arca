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
use App\Repository\DepartmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DepartmentRepository::class)]
#[ORM\Table(name: '`department`')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'department:get'],
    operations: [
        new Get(
            //security: 'is_granted("ROLE_DEPARTMENT_DETAILS")',
            provider: ItemProvider::class
        ),
        new GetCollection(
            //security: 'is_granted("ROLE_DEPARTMENT_LIST")',
            provider: CollectionProvider::class
        ),
        new Post(
            security: 'is_granted("ROLE_DEPARTMENT_CREATE")',
            denormalizationContext: ['groups' => 'department:post'],
            processor: PersistProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_DEPARTMENT_UPDATE")',
            denormalizationContext: ['groups' => 'department:patch'],
            processor: PersistProcessor::class,
        ),
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'name' => 'ipartial',
    'code' => 'ipartial',
    'managerId' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'updatedAt'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'updatedAt'])]
class Department implements RessourceInterface
{
    public const string ID_PREFIX = 'DP';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'DP_ID', length: 16)]
    #[Groups(['department:get'])]
    private ?string $id = null;

    #[ORM\Column(name: 'DP_NAME', length: 120)]
    #[Groups(['department:get', 'department:post', 'department:patch'])]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(name: 'DP_CODE', length: 40)]
    #[Groups(['department:get', 'department:post', 'department:patch'])]
    #[Assert\NotBlank]
    private ?string $code = null;

    #[ORM\Column(name: 'DP_DESCRIPTION', type: Types::TEXT, nullable: true)]
    #[Groups(['department:get', 'department:post', 'department:patch'])]
    private ?string $description = null;

    #[ORM\Column(name: 'DP_MANAGER_ID', length: 16, nullable: true)]
    #[Groups(['department:get', 'department:post', 'department:patch'])]
    private ?string $managerId = null;

    #[ORM\Column(name: 'DP_CREATED_AT')]
    #[Groups(['department:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'DP_UPDATED_AT', nullable: true)]
    #[Groups(['department:get'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?string
    {
        return $this->id;
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

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

    public function getManagerId(): ?string
    {
        return $this->managerId;
    }

    public function setManagerId(?string $managerId): static
    {
        $this->managerId = $managerId;

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
