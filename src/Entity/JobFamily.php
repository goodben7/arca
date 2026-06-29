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
use App\Repository\JobFamilyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: JobFamilyRepository::class)]
#[ORM\Table(name: '`job_family`')]
#[ORM\UniqueConstraint(name: 'UNIQ_JOB_FAMILY_CODE', fields: ['code'])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'job_family:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_JOB_FAMILY_DETAILS")',
            provider: ItemProvider::class,
        ),
        new GetCollection(
            security: 'is_granted("ROLE_JOB_FAMILY_LIST")',
            provider: CollectionProvider::class,
        ),
        new Post(
            security: 'is_granted("ROLE_JOB_FAMILY_CREATE")',
            denormalizationContext: ['groups' => 'job_family:post'],
            processor: PersistProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_JOB_FAMILY_UPDATE")',
            denormalizationContext: ['groups' => 'job_family:patch'],
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
class JobFamily implements RessourceInterface
{
    public const string ID_PREFIX = 'JF';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'JF_ID', length: 16)]
    #[Groups(['job_family:get', 'job_role:get'])]
    private ?string $id = null;

    #[ORM\Column(name: 'JF_CODE', length: 40)]
    #[Groups(['job_family:get', 'job_family:post', 'job_family:patch', 'job_role:get'])]
    #[Assert\NotBlank]
    private ?string $code = null;

    #[ORM\Column(name: 'JF_NAME', length: 120)]
    #[Groups(['job_family:get', 'job_family:post', 'job_family:patch', 'job_role:get'])]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(name: 'JF_DESCRIPTION', type: Types::TEXT, nullable: true)]
    #[Groups(['job_family:get', 'job_family:post', 'job_family:patch', 'job_role:get'])]
    private ?string $description = null;

    #[ORM\Column(name: 'JF_CREATED_AT')]
    #[Groups(['job_family:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'JF_UPDATED_AT', nullable: true)]
    #[Groups(['job_family:get'])]
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
