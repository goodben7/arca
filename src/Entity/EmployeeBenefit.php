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
use App\Doctrine\IdGenerator;
use App\Dto\CreateEmployeeBenefitDto;
use App\Model\EmployeeBenefitConstants;
use App\Model\RessourceInterface;
use App\Repository\EmployeeBenefitRepository;
use App\State\CreateEmployeeBenefitProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EmployeeBenefitRepository::class)]
#[ORM\Table(name: '`employee_benefit`')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'employee_benefit:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_EMPLOYEE_BENEFIT_DETAILS")',
            provider: ItemProvider::class,
        ),
        new GetCollection(
            security: 'is_granted("ROLE_EMPLOYEE_BENEFIT_LIST")',
            provider: CollectionProvider::class,
        ),
        new Post(
            security: 'is_granted("ROLE_EMPLOYEE_BENEFIT_CREATE")',
            input: CreateEmployeeBenefitDto::class,
            processor: CreateEmployeeBenefitProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_EMPLOYEE_BENEFIT_UPDATE")',
            denormalizationContext: ['groups' => 'employee_benefit:patch'],
            processor: \ApiPlatform\Doctrine\Common\State\PersistProcessor::class,
        ),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'employee' => 'exact',
    'benefit' => 'exact',
    'status' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['startDate', 'endDate', 'createdAt'])]
#[ApiFilter(DateFilter::class, properties: ['startDate', 'endDate', 'createdAt'])]
class EmployeeBenefit implements RessourceInterface
{
    public const string ID_PREFIX = 'EB';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'EB_ID', length: 16)]
    #[Groups(['employee_benefit:get'])]
    private ?string $id = null;

    #[ORM\Column(name: 'EB_EMPLOYEE', length: 16)]
    #[Groups(['employee_benefit:get'])]
    #[Assert\NotBlank]
    private ?string $employee = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'EB_BENEFIT', referencedColumnName: 'BF_ID', nullable: false)]
    #[Groups(['employee_benefit:get'])]
    #[Assert\NotNull]
    private ?Benefit $benefit = null;

    #[ORM\Column(name: 'EB_START_DATE', type: Types::DATE_IMMUTABLE)]
    #[Groups(['employee_benefit:get', 'employee_benefit:patch'])]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(name: 'EB_END_DATE', type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['employee_benefit:get', 'employee_benefit:patch'])]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\Column(name: 'EB_STATUS', length: 10)]
    #[Groups(['employee_benefit:get', 'employee_benefit:patch'])]
    #[Assert\Choice(callback: [EmployeeBenefitConstants::class, 'getStatuses'])]
    private ?string $status = null;

    #[ORM\Column(name: 'EB_CREATED_AT')]
    #[Groups(['employee_benefit:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'EB_UPDATED_AT', nullable: true)]
    #[Groups(['employee_benefit:get'])]
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

    public function getBenefit(): ?Benefit
    {
        return $this->benefit;
    }

    public function setBenefit(Benefit $benefit): static
    {
        $this->benefit = $benefit;

        return $this;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeImmutable $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

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
