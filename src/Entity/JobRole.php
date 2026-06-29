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
use App\Repository\JobRoleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Fiche métier du référentiel RH (titre, famille, grade).
 *
 * Distinct de `Position` qui représente un slot organisationnel (effectif / headcount).
 */
#[ORM\Entity(repositoryClass: JobRoleRepository::class)]
#[ORM\Table(name: '`job_role`')]
#[ORM\UniqueConstraint(name: 'UNIQ_JOB_ROLE_CODE', fields: ['code'])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'job_role:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_JOB_ROLE_DETAILS")',
            provider: ItemProvider::class,
        ),
        new GetCollection(
            security: 'is_granted("ROLE_JOB_ROLE_LIST")',
            provider: CollectionProvider::class,
        ),
        new Post(
            security: 'is_granted("ROLE_JOB_ROLE_CREATE")',
            denormalizationContext: ['groups' => 'job_role:post'],
            processor: PersistProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_JOB_ROLE_UPDATE")',
            denormalizationContext: ['groups' => 'job_role:patch'],
            processor: PersistProcessor::class,
        ),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'code' => 'ipartial',
    'title' => 'ipartial',
    'jobFamily' => 'exact',
    'grade' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'updatedAt', 'title', 'code'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'updatedAt'])]
class JobRole implements RessourceInterface
{
    public const string ID_PREFIX = 'JR';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'JR_ID', length: 16)]
    #[Groups(['job_role:get', 'job_offer:get', 'recruitment_request:get', 'employee:get'])]
    private ?string $id = null;

    #[ORM\Column(name: 'JR_CODE', length: 40)]
    #[Groups(['job_role:get', 'job_role:post', 'job_role:patch', 'job_offer:get', 'recruitment_request:get', 'employee:get'])]
    #[Assert\NotBlank]
    private ?string $code = null;

    #[ORM\Column(name: 'JR_TITLE', length: 120)]
    #[Groups(['job_role:get', 'job_role:post', 'job_role:patch', 'job_offer:get', 'recruitment_request:get', 'employee:get'])]
    #[Assert\NotBlank]
    private ?string $title = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'JR_JOB_FAMILY', referencedColumnName: 'JF_ID', nullable: false)]
    #[Groups(['job_role:get', 'job_role:post', 'job_role:patch'])]
    #[Assert\NotNull]
    private ?JobFamily $jobFamily = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'JR_GRADE', referencedColumnName: 'GR_ID', nullable: false)]
    #[Groups(['job_role:get', 'job_role:post', 'job_role:patch'])]
    #[Assert\NotNull]
    private ?Grade $grade = null;

    #[ORM\Column(name: 'JR_DESCRIPTION', type: Types::TEXT, nullable: true)]
    #[Groups(['job_role:get', 'job_role:post', 'job_role:patch'])]
    private ?string $description = null;

    #[ORM\Column(name: 'JR_CREATED_AT')]
    #[Groups(['job_role:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'JR_UPDATED_AT', nullable: true)]
    #[Groups(['job_role:get'])]
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getJobFamily(): ?JobFamily
    {
        return $this->jobFamily;
    }

    public function setJobFamily(JobFamily $jobFamily): static
    {
        $this->jobFamily = $jobFamily;

        return $this;
    }

    public function getGrade(): ?Grade
    {
        return $this->grade;
    }

    public function setGrade(Grade $grade): static
    {
        $this->grade = $grade;

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
