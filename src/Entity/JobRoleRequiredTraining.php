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
use App\Repository\JobRoleRequiredTrainingRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: JobRoleRequiredTrainingRepository::class)]
#[ORM\Table(name: '`job_role_required_training`')]
#[ORM\UniqueConstraint(name: 'UNIQ_JOB_ROLE_REQUIRED_TRAINING', fields: ['jobRole', 'catalogItem'])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'job_role_required_training:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_JOB_ROLE_REQUIRED_TRAINING_DETAILS")',
            provider: ItemProvider::class,
        ),
        new GetCollection(
            security: 'is_granted("ROLE_JOB_ROLE_REQUIRED_TRAINING_LIST")',
            provider: CollectionProvider::class,
        ),
        new Post(
            security: 'is_granted("ROLE_JOB_ROLE_REQUIRED_TRAINING_CREATE")',
            denormalizationContext: ['groups' => 'job_role_required_training:post'],
            processor: PersistProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_JOB_ROLE_REQUIRED_TRAINING_UPDATE")',
            denormalizationContext: ['groups' => 'job_role_required_training:patch'],
            processor: PersistProcessor::class,
        ),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'jobRole' => 'exact',
    'catalogItem' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'updatedAt'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'updatedAt'])]
class JobRoleRequiredTraining implements RessourceInterface
{
    public const string ID_PREFIX = 'JRT';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'JRT_ID', length: 16)]
    #[Groups(['job_role_required_training:get'])]
    private ?string $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'JRT_JOB_ROLE', referencedColumnName: 'JR_ID', nullable: false)]
    #[Groups(['job_role_required_training:get', 'job_role_required_training:post', 'job_role_required_training:patch'])]
    #[Assert\NotNull]
    private ?JobRole $jobRole = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'JRT_CATALOG_ITEM', referencedColumnName: 'TC_ID', nullable: false)]
    #[Groups(['job_role_required_training:get', 'job_role_required_training:post', 'job_role_required_training:patch'])]
    #[Assert\NotNull]
    private ?TrainingCatalog $catalogItem = null;

    #[ORM\Column(name: 'JRT_CREATED_AT')]
    #[Groups(['job_role_required_training:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'JRT_UPDATED_AT', nullable: true)]
    #[Groups(['job_role_required_training:get'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getJobRole(): ?JobRole
    {
        return $this->jobRole;
    }

    public function setJobRole(JobRole $jobRole): static
    {
        $this->jobRole = $jobRole;

        return $this;
    }

    public function getCatalogItem(): ?TrainingCatalog
    {
        return $this->catalogItem;
    }

    public function setCatalogItem(TrainingCatalog $catalogItem): static
    {
        $this->catalogItem = $catalogItem;

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
