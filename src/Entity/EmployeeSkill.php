<?php

namespace App\Entity;

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
use App\Dto\CreateEmployeeSkillDto;
use App\Dto\ValidateEmployeeSkillDto;
use App\Doctrine\IdGenerator;
use App\Model\RessourceInterface;
use App\Model\SkillConstants;
use App\Repository\EmployeeSkillRepository;
use App\State\CreateEmployeeSkillProcessor;
use App\State\UpdateEmployeeSkillProcessor;
use App\State\ValidateEmployeeSkillProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EmployeeSkillRepository::class)]
#[ORM\Table(name: '`employee_skill`')]
#[ORM\UniqueConstraint(name: 'UNIQ_EMPLOYEE_SKILL', fields: ['employee', 'skill'])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'employee_skill:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_EMPLOYEE_SKILL_DETAILS")',
            provider: ItemProvider::class,
        ),
        new GetCollection(
            security: 'is_granted("ROLE_EMPLOYEE_SKILL_LIST")',
            provider: CollectionProvider::class,
        ),
        new Post(
            security: 'is_granted("ROLE_EMPLOYEE_SKILL_CREATE")',
            input: CreateEmployeeSkillDto::class,
            processor: CreateEmployeeSkillProcessor::class,
        ),
        new Post(
            uriTemplate: '/employee_skills/validations',
            security: 'is_granted("ROLE_EMPLOYEE_SKILL_UPDATE")',
            input: ValidateEmployeeSkillDto::class,
            processor: ValidateEmployeeSkillProcessor::class,
            status: 200,
        ),
        new Patch(
            security: 'is_granted("ROLE_EMPLOYEE_SKILL_UPDATE")',
            denormalizationContext: ['groups' => 'employee_skill:patch'],
            processor: UpdateEmployeeSkillProcessor::class,
        ),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'employee' => 'exact',
    'skill' => 'exact',
    'level' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'updatedAt', 'validatedAt'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'updatedAt', 'validatedAt'])]
class EmployeeSkill implements RessourceInterface
{
    public const string ID_PREFIX = 'ES';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'ES_ID', length: 16)]
    #[Groups(['employee_skill:get'])]
    private ?string $id = null;

    #[ORM\Column(name: 'ES_EMPLOYEE', length: 16)]
    #[Groups(['employee_skill:get'])]
    #[Assert\NotBlank]
    private ?string $employee = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'ES_SKILL', referencedColumnName: 'SK_ID', nullable: false)]
    #[Groups(['employee_skill:get'])]
    #[Assert\NotNull]
    private ?Skill $skill = null;

    #[ORM\Column(name: 'ES_LEVEL', length: 15)]
    #[Groups(['employee_skill:get', 'employee_skill:patch'])]
    #[Assert\Choice(callback: [SkillConstants::class, 'getLevels'])]
    #[Assert\NotBlank]
    private ?string $level = null;

    #[ORM\Column(name: 'ES_VALIDATED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['employee_skill:get', 'employee_skill:patch'])]
    private ?\DateTimeImmutable $validatedAt = null;

    #[ORM\Column(name: 'ES_CREATED_AT')]
    #[Groups(['employee_skill:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'ES_UPDATED_AT', nullable: true)]
    #[Groups(['employee_skill:get'])]
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

    public function getSkill(): ?Skill
    {
        return $this->skill;
    }

    public function setSkill(Skill $skill): static
    {
        $this->skill = $skill;

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

    public function getValidatedAt(): ?\DateTimeImmutable
    {
        return $this->validatedAt;
    }

    public function setValidatedAt(?\DateTimeImmutable $validatedAt): static
    {
        $this->validatedAt = $validatedAt;

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
