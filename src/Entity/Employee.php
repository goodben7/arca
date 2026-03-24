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
use App\Dto\ActivateEmployeeDto;
use App\Dto\AssignManagerEmployeeDto;
use App\Dto\DeactivateEmployeeDto;
use App\Dto\PutEmployeeOnLeaveDto;
use App\Dto\PutEmployeeOnProbationDto;
use App\Dto\RetireEmployeeDto;
use App\Dto\SuspendEmployeeDto;
use App\Dto\TerminateEmployeeDto;
use App\Dto\CreateEmployeeDto;
use App\Model\EmployeeConstants;
use App\Model\RessourceInterface;
use App\Repository\EmployeeRepository;
use App\State\ActivateEmployeeProcessor;
use App\State\AssignManagerEmployeeProcessor;
use App\State\DeactivateEmployeeProcessor;
use App\State\CreateEmployeeProcessor;
use App\State\PutEmployeeOnLeaveProcessor;
use App\State\PutEmployeeOnProbationProcessor;
use App\State\RetireEmployeeProcessor;
use App\State\SuspendEmployeeProcessor;
use App\State\TerminateEmployeeProcessor;
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
        new Post(
            uriTemplate: '/employees/activations',
            security: 'is_granted("ROLE_EMPLOYEE_ACTIVATE")',
            input: ActivateEmployeeDto::class,
            processor: ActivateEmployeeProcessor::class,    
            status: 200
        ),
        new Post(
            uriTemplate: '/employees/deactivations',
            security: 'is_granted("ROLE_EMPLOYEE_DEACTIVATE")',
            input: DeactivateEmployeeDto::class,
            processor: DeactivateEmployeeProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/employees/on_leaves',
            security: 'is_granted("ROLE_EMPLOYEE_SET_ON_LEAVE")',
            input: PutEmployeeOnLeaveDto::class,
            processor: PutEmployeeOnLeaveProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/employees/suspensions',
            security: 'is_granted("ROLE_EMPLOYEE_SUSPEND")',
            input: SuspendEmployeeDto::class,
            processor: SuspendEmployeeProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/employees/terminations',
            security: 'is_granted("ROLE_EMPLOYEE_TERMINATE")',
            input: TerminateEmployeeDto::class,
            processor: TerminateEmployeeProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/employees/retirements',
            security: 'is_granted("ROLE_EMPLOYEE_RETIRE")',
            input: RetireEmployeeDto::class,
            processor: RetireEmployeeProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/employees/probations',
            security: 'is_granted("ROLE_EMPLOYEE_SET_PROBATION")',
            input: PutEmployeeOnProbationDto::class,
            processor: PutEmployeeOnProbationProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/employees/assign-manager',
            security: 'is_granted("ROLE_EMPLOYEE_ASSIGN_MANAGER")',
            input: AssignManagerEmployeeDto::class,
            processor: AssignManagerEmployeeProcessor::class,
            status: 200
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

    #[ORM\Column(name: 'EM_ACTIVATED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['employee:get'])]
    private ?\DateTimeImmutable $activatedAt = null;

    #[ORM\Column(name: 'EM_ACTIVATED_BY', length: 16, nullable: true)]
    #[Groups(['employee:get'])]
    private ?string $activatedBy = null;

    #[ORM\Column(name: 'EM_DEACTIVATED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['employee:get'])]
    private ?\DateTimeImmutable $deactivatedAt = null;

    #[ORM\Column(name: 'EM_DEACTIVATED_BY', length: 16, nullable: true)]
    #[Groups(['employee:get'])]
    private ?string $deactivatedBy = null;

    #[ORM\Column(name: 'EM_ON_LEAVE_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['employee:get'])]
    private ?\DateTimeImmutable $onLeaveAt = null;

    #[ORM\Column(name: 'EM_ON_LEAVE_BY', length: 16, nullable: true)]
    #[Groups(['employee:get'])]
    private ?string $onLeaveBy = null;

    #[ORM\Column(name: 'EM_SUSPENDED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['employee:get'])]
    private ?\DateTimeImmutable $suspendedAt = null;

    #[ORM\Column(name: 'EM_SUSPENDED_BY', length: 16, nullable: true)]
    #[Groups(['employee:get'])]
    private ?string $suspendedBy = null;

    #[ORM\Column(name: 'EM_TERMINATED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['employee:get'])]
    private ?\DateTimeImmutable $terminatedAt = null;

    #[ORM\Column(name: 'EM_TERMINATED_BY', length: 16, nullable: true)]
    #[Groups(['employee:get'])]
    private ?string $terminatedBy = null;

    #[ORM\Column(name: 'EM_RETIRED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['employee:get'])]
    private ?\DateTimeImmutable $retiredAt = null;

    #[ORM\Column(name: 'EM_RETIRED_BY', length: 16, nullable: true)]
    #[Groups(['employee:get'])]
    private ?string $retiredBy = null;

    #[ORM\Column(name: 'EM_PROBATION_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['employee:get'])]
    private ?\DateTimeImmutable $probationAt = null;

    #[ORM\Column(name: 'EM_PROBATION_BY', length: 16, nullable: true)]
    #[Groups(['employee:get'])]
    private ?string $probationBy = null;

    #[ORM\Column(name: 'EM_MANAGER_ASSIGNED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['employee:get'])]
    private ?\DateTimeImmutable $managerAssignedAt = null;

    #[ORM\Column(name: 'EM_MANAGER_ASSIGNED_BY', length: 16, nullable: true)]
    #[Groups(['employee:get'])]
    private ?string $managerAssignedBy = null;

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
    public function getActivatedAt(): ?\DateTimeImmutable { return $this->activatedAt; }
    public function setActivatedAt(?\DateTimeImmutable $activatedAt): static { $this->activatedAt = $activatedAt; return $this; }
    public function getActivatedBy(): ?string { return $this->activatedBy; }
    public function setActivatedBy(?string $activatedBy): static { $this->activatedBy = $activatedBy; return $this; }
    public function getDeactivatedAt(): ?\DateTimeImmutable { return $this->deactivatedAt; }
    public function setDeactivatedAt(?\DateTimeImmutable $deactivatedAt): static { $this->deactivatedAt = $deactivatedAt; return $this; }
    public function getDeactivatedBy(): ?string { return $this->deactivatedBy; }
    public function setDeactivatedBy(?string $deactivatedBy): static { $this->deactivatedBy = $deactivatedBy; return $this; }
    public function getOnLeaveAt(): ?\DateTimeImmutable { return $this->onLeaveAt; }
    public function setOnLeaveAt(?\DateTimeImmutable $onLeaveAt): static { $this->onLeaveAt = $onLeaveAt; return $this; }
    public function getOnLeaveBy(): ?string { return $this->onLeaveBy; }
    public function setOnLeaveBy(?string $onLeaveBy): static { $this->onLeaveBy = $onLeaveBy; return $this; }
    public function getSuspendedAt(): ?\DateTimeImmutable { return $this->suspendedAt; }
    public function setSuspendedAt(?\DateTimeImmutable $suspendedAt): static { $this->suspendedAt = $suspendedAt; return $this; }
    public function getSuspendedBy(): ?string { return $this->suspendedBy; }
    public function setSuspendedBy(?string $suspendedBy): static { $this->suspendedBy = $suspendedBy; return $this; }
    public function getTerminatedAt(): ?\DateTimeImmutable { return $this->terminatedAt; }
    public function setTerminatedAt(?\DateTimeImmutable $terminatedAt): static { $this->terminatedAt = $terminatedAt; return $this; }
    public function getTerminatedBy(): ?string { return $this->terminatedBy; }
    public function setTerminatedBy(?string $terminatedBy): static { $this->terminatedBy = $terminatedBy; return $this; }
    public function getRetiredAt(): ?\DateTimeImmutable { return $this->retiredAt; }
    public function setRetiredAt(?\DateTimeImmutable $retiredAt): static { $this->retiredAt = $retiredAt; return $this; }
    public function getRetiredBy(): ?string { return $this->retiredBy; }
    public function setRetiredBy(?string $retiredBy): static { $this->retiredBy = $retiredBy; return $this; }
    public function getProbationAt(): ?\DateTimeImmutable { return $this->probationAt; }
    public function setProbationAt(?\DateTimeImmutable $probationAt): static { $this->probationAt = $probationAt; return $this; }
    public function getProbationBy(): ?string { return $this->probationBy; }
    public function setProbationBy(?string $probationBy): static { $this->probationBy = $probationBy; return $this; }
    public function getManagerAssignedAt(): ?\DateTimeImmutable { return $this->managerAssignedAt; }
    public function setManagerAssignedAt(?\DateTimeImmutable $managerAssignedAt): static { $this->managerAssignedAt = $managerAssignedAt; return $this; }
    public function getManagerAssignedBy(): ?string { return $this->managerAssignedBy; }
    public function setManagerAssignedBy(?string $managerAssignedBy): static { $this->managerAssignedBy = $managerAssignedBy; return $this; }
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
