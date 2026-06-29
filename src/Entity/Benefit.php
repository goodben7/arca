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
use App\Model\BenefitConstants;
use App\Model\RessourceInterface;
use App\Repository\BenefitRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BenefitRepository::class)]
#[ORM\Table(name: '`benefit`')]
#[ORM\UniqueConstraint(name: 'UNIQ_BENEFIT_CODE', fields: ['code'])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'benefit:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_BENEFIT_DETAILS")',
            provider: ItemProvider::class,
        ),
        new GetCollection(
            security: 'is_granted("ROLE_BENEFIT_LIST")',
            provider: CollectionProvider::class,
        ),
        new Post(
            security: 'is_granted("ROLE_BENEFIT_CREATE")',
            denormalizationContext: ['groups' => 'benefit:post'],
            processor: PersistProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_BENEFIT_UPDATE")',
            denormalizationContext: ['groups' => 'benefit:patch'],
            processor: PersistProcessor::class,
        ),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'code' => 'ipartial',
    'name' => 'ipartial',
    'type' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['name', 'code', 'createdAt'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt'])]
class Benefit implements RessourceInterface
{
    public const string ID_PREFIX = 'BF';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'BF_ID', length: 16)]
    #[Groups(['benefit:get', 'employee_benefit:get'])]
    private ?string $id = null;

    #[ORM\Column(name: 'BF_CODE', length: 40)]
    #[Groups(['benefit:get', 'benefit:post', 'benefit:patch', 'employee_benefit:get'])]
    #[Assert\NotBlank]
    private ?string $code = null;

    #[ORM\Column(name: 'BF_NAME', length: 120)]
    #[Groups(['benefit:get', 'benefit:post', 'benefit:patch', 'employee_benefit:get'])]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(name: 'BF_DESCRIPTION', type: Types::TEXT, nullable: true)]
    #[Groups(['benefit:get', 'benefit:post', 'benefit:patch'])]
    private ?string $description = null;

    #[ORM\Column(name: 'BF_TYPE', length: 15)]
    #[Groups(['benefit:get', 'benefit:post', 'benefit:patch', 'employee_benefit:get'])]
    #[Assert\Choice(callback: [BenefitConstants::class, 'getTypes'])]
    #[Assert\NotBlank]
    private ?string $type = null;

    #[ORM\Column(name: 'BF_CREATED_AT')]
    #[Groups(['benefit:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'BF_UPDATED_AT', nullable: true)]
    #[Groups(['benefit:get'])]
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

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
