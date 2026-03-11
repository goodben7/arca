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
use App\Dto\CreateWorkExperienceDto;
use App\Model\RessourceInterface;
use App\Repository\WorkExperienceRepository;
use App\State\CreateWorkExperienceProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: WorkExperienceRepository::class)]
#[ORM\Table(name: '`work_experience`')]
#[ApiResource(
    normalizationContext: ['groups' => 'work_experience:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_WORK_EXPERIENCE_DETAILS")',
            provider: ItemProvider::class
        ),
        new GetCollection(
            security: 'is_granted("ROLE_WORK_EXPERIENCE_LIST")',
            provider: CollectionProvider::class
        ),
        new Post(
            security: 'is_granted("ROLE_WORK_EXPERIENCE_CREATE")',
            input: CreateWorkExperienceDto::class,
            processor: CreateWorkExperienceProcessor::class
        ),
        new Patch(
            security: 'is_granted("ROLE_WORK_EXPERIENCE_UPDATE")',
            denormalizationContext: ['groups' => 'work_experience:patch',],
            processor: PersistProcessor::class,
        ),
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'employee' => 'exact',
    'company' => 'ipartial',
    'position' => 'ipartial',
    'isInternal' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['startDate', 'endDate'])]
#[ApiFilter(DateFilter::class, properties: ['startDate', 'endDate'])]
class WorkExperience implements RessourceInterface
{
    public const string ID_PREFIX = 'WE';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'WE_ID', length: 16)]
    #[Groups(['work_experience:get'])]
    private ?string $id = null;

    #[ORM\Column(name: 'WE_EMPLOYEE', length: 16)]
    #[Groups(['work_experience:get'])]
    private ?string $employee = null;

    #[ORM\Column(name: 'WE_COMPANY', length: 180)]
    #[Groups(['work_experience:get', 'work_experience:patch'])]
    private ?string $company = null;

    #[ORM\Column(name: 'WE_POSITION', length: 120)]
    #[Groups(['work_experience:get', 'work_experience:patch'])]
    private ?string $position = null;

    #[ORM\Column(name: 'WE_START_DATE', type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['work_experience:get', 'work_experience:patch'])]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(name: 'WE_END_DATE', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['work_experience:get', 'work_experience:patch'])]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(name: 'WE_DESCRIPTION', type: Types::TEXT, nullable: true)]
    #[Groups(['work_experience:get', 'work_experience:patch'])]
    private ?string $description = null;

    #[ORM\Column(name: 'WE_IS_INTERNAL', options: ['default' => false])]
    #[Groups(['work_experience:get', 'work_experience:patch'])]
    private ?bool $isInternal = false;

    public function getId(): ?string { return $this->id; }

    public function getEmployee(): ?string { return $this->employee; }
    public function setEmployee(string $employee): static { $this->employee = $employee; return $this; }

    public function getCompany(): ?string { return $this->company; }
    public function setCompany(string $company): static { $this->company = $company; return $this; }

    public function getPosition(): ?string { return $this->position; }
    public function setPosition(string $position): static { $this->position = $position; return $this; }

    public function getStartDate(): ?\DateTimeInterface { return $this->startDate; }
    public function setStartDate(\DateTimeInterface $startDate): static { $this->startDate = $startDate; return $this; }

    public function getEndDate(): ?\DateTimeInterface { return $this->endDate; }
    public function setEndDate(?\DateTimeInterface $endDate): static { $this->endDate = $endDate; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function isInternal(): ?bool { return $this->isInternal; }
    public function setIsInternal(bool $isInternal): static { $this->isInternal = $isInternal; return $this; }
}
