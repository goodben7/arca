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
use App\Dto\CreateEmployeeDto;
use App\Model\EmployeeConstants;
use App\Model\RessourceInterface;
use App\Repository\EmployeeRepository;
use App\State\CreateEmployeeProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EmployeeRepository::class)] 
#[ORM\Table(name: '`employee`')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'employee:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_EMPLOYEE_DETAILS")',
            provider: ItemProvider::class
        ),
        new GetCollection(
            security: 'is_granted("ROLE_EMPLOYEE_LIST")',
            provider: CollectionProvider::class
        ),
        new Post(
            security: 'is_granted("ROLE_EMPLOYEE_CREATE")',
            input: CreateEmployeeDto::class,
            processor: CreateEmployeeProcessor::class
        ),
        new Patch(
            security: 'is_granted("ROLE_EMPLOYEE_UPDATE")',
            denormalizationContext: ['groups' => 'employee:patch',],
            processor: PersistProcessor::class,
        ),
    ]
)]
#[ApiFilter(\ApiPlatform\Doctrine\Orm\Filter\SearchFilter::class, properties: [
    'id' => 'exact',
    'employeeNumber' => 'exact',
    'email' => 'exact',
    'phone' => 'exact',
    'firstName' => 'ipartial',
    'lastName' => 'ipartial',
    'status' => 'exact',
])]
#[ApiFilter(\ApiPlatform\Doctrine\Orm\Filter\OrderFilter::class, properties: ['createdAt', 'updatedAt', 'hireDate', 'departureDate'])]
#[ApiFilter(\ApiPlatform\Doctrine\Orm\Filter\DateFilter::class, properties: ['birthDate', 'hireDate', 'departureDate', 'createdAt', 'updatedAt'])]
class Employee implements RessourceInterface
{
    public const string ID_PREFIX = "EM";

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'EM_ID', length: 16)]
    #[Groups(['employee:get'])]
    private ?string $id = null;

    #[ORM\Column(name: 'EM_EMPLOYEE_NUMBER', length: 30, unique: true)]
    #[Groups(['employee:get',])]
    private ?string $employeeNumber = null;

    #[ORM\Column(name: 'EM_FIRST_NAME', length: 80)]
    #[Groups(['employee:get', 'employee:patch'])]
    private ?string $firstName = null;

    #[ORM\Column(name: 'EM_LAST_NAME', length: 80)]
    #[Groups(['employee:get', 'employee:patch'])]
    private ?string $lastName = null;

    #[ORM\Column(name: 'EM_EMAIL', length: 180, nullable: true)]
    #[Groups(['employee:get', 'employee:patch'])]
    private ?string $email = null;

    #[ORM\Column(name: 'EM_PHONE', length: 15, nullable: true)]
    #[Groups(['employee:get', 'employee:patch'])]
    private ?string $phone = null;

    #[ORM\Column(name: 'EM_GENDER', length: 10)]
    #[Assert\Choice(callback: [EmployeeConstants::class, 'getGenders'])]
    #[Groups(['employee:get', 'employee:patch'])]
    private ?string $gender = null;

    #[ORM\Column(name: 'EM_BIRTH_DATE', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['employee:get', 'employee:patch'])]
    private ?\DateTimeInterface $birthDate = null;

    #[ORM\Column(name: 'EM_NATIONALITY', length: 60, nullable: true)]
    #[Groups(['employee:get', 'employee:patch'])]
    private ?string $nationality = null;

    #[ORM\Column(name: 'EM_MARITAL_STATUS', length: 15, nullable: true)]
    #[Assert\Choice(callback: [EmployeeConstants::class, 'getMaritalStatuses'])]
    #[Groups(['employee:get', 'employee:patch'])]
    private ?string $maritalStatus = null;

    #[ORM\Column(name: 'EM_HIRE_DATE', type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['employee:get', 'employee:patch'])]
    private ?\DateTimeInterface $hireDate = null;

    #[ORM\Column(name: 'EM_DEPARTURE_DATE', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['employee:get', 'employee:patch'])]
    private ?\DateTimeInterface $departureDate = null;

    #[ORM\Column(name: 'EM_STATUS', length: 15)]
    #[Assert\Choice(callback: [EmployeeConstants::class, 'getStatuses'])]
    #[Groups(['employee:get', 'employee:patch'])]
    private ?string $status = null;

    #[ORM\Column(name: 'EM_DEPARTMENT', length: 120, nullable: true)]
    #[Groups(['employee:get', 'employee:patch'])]
    private ?string $department = null;

    #[ORM\Column(name: 'EM_POSITION', length: 120, nullable: true)]
    #[Groups(['employee:get', 'employee:patch'])]
    private ?string $position = null;

    #[ORM\Column(name: 'EM_MANAGER', nullable: true, length: 16)]
    #[Groups(['employee:get', 'employee:patch'])]
    private ?string $manager = null;

    #[ORM\Column(name: 'EM_CREATED_BY', length: 16, nullable: true)]
    #[Groups(['employee:get'])]
    private ?string $createdBy = null;

    #[ORM\Column(name: 'EM_CREATED_AT')]
    #[Groups(['employee:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'EM_UPDATED_AT', nullable: true)]
    #[Groups(['employee:get'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(name: 'EM_USER_ID', length: 16, nullable: true)]
    #[Groups(['employee:get'])]
    private ?string $userId = null;

    #[ORM\Column(length: 120, nullable: true, name: 'EM_DISPLAY_NAME')]
    #[Groups(['employee:get'])]
    private ?string $displayName = null;

    public function getId(): ?string { return $this->id; }
    public function getEmployeeNumber(): ?string { return $this->employeeNumber; }
    public function setEmployeeNumber(string $employeeNumber): static { $this->employeeNumber = $employeeNumber; return $this; }
    public function getFirstName(): ?string { return $this->firstName; }
    public function setFirstName(string $firstName): static { $this->firstName = $firstName; return $this; }
    public function getLastName(): ?string { return $this->lastName; }
    public function setLastName(string $lastName): static { $this->lastName = $lastName; return $this; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): static { $this->email = $email; return $this; }
    public function getPhone(): ?string { return $this->phone; }
    public function setPhone(?string $phone): static { $this->phone = $phone; return $this; }
    public function getGender(): ?string { return $this->gender; }
    public function setGender(string $gender): static { $this->gender = $gender; return $this; }
    public function getBirthDate(): ?\DateTimeInterface { return $this->birthDate; }
    public function setBirthDate(?\DateTimeInterface $birthDate): static { $this->birthDate = $birthDate; return $this; }
    public function getNationality(): ?string { return $this->nationality; }
    public function setNationality(?string $nationality): static { $this->nationality = $nationality; return $this; }
    public function getMaritalStatus(): ?string { return $this->maritalStatus; }
    public function setMaritalStatus(?string $maritalStatus): static { $this->maritalStatus = $maritalStatus; return $this; }
    public function getHireDate(): ?\DateTimeInterface { return $this->hireDate; }
    public function setHireDate(\DateTimeInterface $hireDate): static { $this->hireDate = $hireDate; return $this; }
    public function getDepartureDate(): ?\DateTimeInterface { return $this->departureDate; }
    public function setDepartureDate(?\DateTimeInterface $departureDate): static { $this->departureDate = $departureDate; return $this; }
    public function getStatus(): ?string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }
    public function getDepartment(): ?string { return $this->department; }
    public function setDepartment(?string $department): static { $this->department = $department; return $this; }
    public function getPosition(): ?string { return $this->position; }
    public function setPosition(?string $position): static { $this->position = $position; return $this; }
    public function getManager(): ?string { return $this->manager; }
    public function setManager(?string $manager): static { $this->manager = $manager; return $this; }
    public function getCreatedBy(): ?string { return $this->createdBy; }
    public function setCreatedBy(?string $createdBy): static { $this->createdBy = $createdBy; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static { $this->updatedAt = $updatedAt; return $this; }

    public function buildDisplayName(): static
    {
        $this->displayName = "{$this->firstName} {$this->lastName}";
        return $this;
    }

    #[ORM\PreUpdate]
    public function touch(): void { $this->updatedAt = new \DateTimeImmutable(); }

    #[ORM\PrePersist]
    public function initCreatedAt(): void { $this->createdAt = new \DateTimeImmutable(); }

    /**
     * Get the value of userId
     */ 
    public function getUserId(): string|null
    {
        return $this->userId;
    }

    /**
     * Set the value of userId
     *
     * @return  self
     */ 
    public function setUserId(?string $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get the value of displayName
     */ 
    public function getDisplayName(): string|null
    {
        return $this->buildDisplayName()->displayName;
    }

    /**
     * Set the value of displayName
     *
     * @return  self
     */ 
    public function setDisplayName(?string $displayName): static
    {
        $this->displayName = $displayName;

        return $this;
    }
}
