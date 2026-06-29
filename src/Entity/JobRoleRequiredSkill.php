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
use App\Model\SkillConstants;
use App\Repository\JobRoleRequiredSkillRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: JobRoleRequiredSkillRepository::class)]
#[ORM\Table(name: '`job_role_required_skill`')]
#[ORM\UniqueConstraint(name: 'UNIQ_JOB_ROLE_REQUIRED_SKILL', fields: ['jobRole', 'skill'])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'job_role_required_skill:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_JOB_ROLE_REQUIRED_SKILL_DETAILS")',
            provider: ItemProvider::class,
        ),
        new GetCollection(
            security: 'is_granted("ROLE_JOB_ROLE_REQUIRED_SKILL_LIST")',
            provider: CollectionProvider::class,
        ),
        new Post(
            security: 'is_granted("ROLE_JOB_ROLE_REQUIRED_SKILL_CREATE")',
            denormalizationContext: ['groups' => 'job_role_required_skill:post'],
            processor: PersistProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_JOB_ROLE_REQUIRED_SKILL_UPDATE")',
            denormalizationContext: ['groups' => 'job_role_required_skill:patch'],
            processor: PersistProcessor::class,
        ),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'jobRole' => 'exact',
    'skill' => 'exact',
    'minimumLevel' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'updatedAt'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'updatedAt'])]
class JobRoleRequiredSkill implements RessourceInterface
{
    public const string ID_PREFIX = 'JRS';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'JRS_ID', length: 16)]
    #[Groups(['job_role_required_skill:get', 'job_role:get'])]
    private ?string $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'JRS_JOB_ROLE', referencedColumnName: 'JR_ID', nullable: false)]
    #[Groups(['job_role_required_skill:get', 'job_role_required_skill:post', 'job_role_required_skill:patch'])]
    #[Assert\NotNull]
    private ?JobRole $jobRole = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'JRS_SKILL', referencedColumnName: 'SK_ID', nullable: false)]
    #[Groups(['job_role_required_skill:get', 'job_role_required_skill:post', 'job_role_required_skill:patch'])]
    #[Assert\NotNull]
    private ?Skill $skill = null;

    #[ORM\Column(name: 'JRS_MINIMUM_LEVEL', length: 15)]
    #[Groups(['job_role_required_skill:get', 'job_role_required_skill:post', 'job_role_required_skill:patch'])]
    #[Assert\Choice(callback: [SkillConstants::class, 'getLevels'])]
    #[Assert\NotBlank]
    private ?string $minimumLevel = null;

    #[ORM\Column(name: 'JRS_CREATED_AT')]
    #[Groups(['job_role_required_skill:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'JRS_UPDATED_AT', nullable: true)]
    #[Groups(['job_role_required_skill:get'])]
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

    public function getSkill(): ?Skill
    {
        return $this->skill;
    }

    public function setSkill(Skill $skill): static
    {
        $this->skill = $skill;

        return $this;
    }

    public function getMinimumLevel(): ?string
    {
        return $this->minimumLevel;
    }

    public function setMinimumLevel(string $minimumLevel): static
    {
        $this->minimumLevel = $minimumLevel;

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
