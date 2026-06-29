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
use ApiPlatform\Metadata\Post;
use App\Doctrine\IdGenerator;
use App\Dto\CertifyTrainingEnrollmentDto;
use App\Dto\CompleteTrainingEnrollmentDto;
use App\Dto\CreateTrainingEnrollmentDto;
use App\Dto\MarkTrainingEnrollmentAbsentDto;
use App\Dto\SetTrainingEnrollmentEnrolledDto;
use App\Dto\StartTrainingEnrollmentDto;
use App\Model\RessourceInterface;
use App\Model\TrainingEnrollmentConstants;
use App\Repository\TrainingEnrollmentRepository;
use App\State\CertifyTrainingEnrollmentProcessor;
use App\State\CreateTrainingEnrollmentProcessor;
use App\State\CompleteTrainingEnrollmentProcessor;
use App\State\MarkTrainingEnrollmentAbsentProcessor;
use App\State\SetTrainingEnrollmentEnrolledProcessor;
use App\State\StartTrainingEnrollmentProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TrainingEnrollmentRepository::class)]
#[ORM\Table(name: '`training_enrollment`')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'training_enrollment:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_TRAINING_ENROLLMENT_DETAILS")',
            provider: ItemProvider::class
        ),
        new GetCollection(
            security: 'is_granted("ROLE_TRAINING_ENROLLMENT_LIST")',
            provider: CollectionProvider::class
        ),
        new Post(
            security: 'is_granted("ROLE_TRAINING_ENROLLMENT_CREATE")',
            input: CreateTrainingEnrollmentDto::class,
            processor: CreateTrainingEnrollmentProcessor::class
        ),
        new Post(
            uriTemplate: '/training_enrollments/starts',
            security: 'is_granted("ROLE_TRAINING_ENROLLMENT_START")',
            input: StartTrainingEnrollmentDto::class,
            processor: StartTrainingEnrollmentProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/training_enrollments/completions',
            security: 'is_granted("ROLE_TRAINING_ENROLLMENT_COMPLETE")',
            input: CompleteTrainingEnrollmentDto::class,
            processor: CompleteTrainingEnrollmentProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/training_enrollments/certifications',
            security: 'is_granted("ROLE_TRAINING_ENROLLMENT_CERTIFY")',
            input: CertifyTrainingEnrollmentDto::class,
            processor: CertifyTrainingEnrollmentProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/training_enrollments/absences',
            security: 'is_granted("ROLE_TRAINING_ENROLLMENT_MARK_ABSENT")',
            input: MarkTrainingEnrollmentAbsentDto::class,
            processor: MarkTrainingEnrollmentAbsentProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/training_enrollments/enrollments',
            security: 'is_granted("ROLE_TRAINING_ENROLLMENT_SET_ENROLLED")',
            input: SetTrainingEnrollmentEnrolledDto::class,
            processor: SetTrainingEnrollmentEnrolledProcessor::class,
            status: 200
        ),
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'employee' => 'exact',
    'trainingSession' => 'exact',
    'status' => 'exact',
    'assignedBy' => 'exact',
    'completedBy' => 'exact',
    'certifiedBy' => 'exact',
    'absentBy' => 'exact',
])]
#[ApiFilter(DateFilter::class, properties: [
    'createdAt',
    'assignedAt',
    'startedAt',
    'completedAt',
    'certifiedAt',
    'absentAt',
])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'assignedAt', 'startedAt', 'completedAt', 'certifiedAt', 'absentAt'])]
class TrainingEnrollment implements RessourceInterface
{
    public const string ID_PREFIX = 'TE';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'TE_ID', length: 16)]
    #[Groups(['training_enrollment:get'])]
    private ?string $id = null;

    #[ORM\Column(name: 'TE_EMPLOYEE', length: 16)]
    #[Groups(['training_enrollment:get'])]
    #[Assert\NotBlank]
    private ?string $employee = null;

    #[ORM\Column(name: 'TE_TRAINING_SESSION', length: 16)]
    #[Groups(['training_enrollment:get'])]
    #[Assert\NotBlank]
    private ?string $trainingSession = null;

    #[ORM\Column(name: 'TE_STATUS', length: 15)]
    #[Groups(['training_enrollment:get'])]
    #[Assert\Choice(callback: [TrainingEnrollmentConstants::class, 'getStatuses'])]
    #[Assert\NotBlank]
    private ?string $status = null;

    #[ORM\Column(name: 'TE_SCORE', type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    #[Groups(['training_enrollment:get'])]
    #[Assert\Range(min: 0, max: 100)]
    private ?string $score = null;

    #[ORM\Column(name: 'TE_CERTIFICATE', length: 255, nullable: true)]
    #[Groups(['training_enrollment:get'])]
    private ?string $certificate = null;

    #[ORM\Column(name: 'TE_ASSIGNED_AT', nullable: true)]
    #[Groups(['training_enrollment:get'])]
    private ?\DateTimeImmutable $assignedAt = null;

    #[ORM\Column(name: 'TE_ASSIGNED_BY', length: 16, nullable: true)]
    #[Groups(['training_enrollment:get'])]
    private ?string $assignedBy = null;

    #[ORM\Column(name: 'TE_STARTED_AT', nullable: true)]
    #[Groups(['training_enrollment:get'])]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(name: 'TE_STARTED_BY', length: 16, nullable: true)]
    #[Groups(['training_enrollment:get'])]
    private ?string $startedBy = null;

    #[ORM\Column(name: 'TE_COMPLETED_AT', nullable: true)]
    #[Groups(['training_enrollment:get'])]
    private ?\DateTimeImmutable $completedAt = null;

    #[ORM\Column(name: 'TE_COMPLETED_BY', length: 16, nullable: true)]
    #[Groups(['training_enrollment:get'])]
    private ?string $completedBy = null;

    #[ORM\Column(name: 'TE_CERTIFIED_AT', nullable: true)]
    #[Groups(['training_enrollment:get'])]
    private ?\DateTimeImmutable $certifiedAt = null;

    #[ORM\Column(name: 'TE_CERTIFIED_BY', length: 16, nullable: true)]
    #[Groups(['training_enrollment:get'])]
    private ?string $certifiedBy = null;

    #[ORM\Column(name: 'TE_ABSENT_AT', nullable: true)]
    #[Groups(['training_enrollment:get'])]
    private ?\DateTimeImmutable $absentAt = null;

    #[ORM\Column(name: 'TE_ABSENT_BY', length: 16, nullable: true)]
    #[Groups(['training_enrollment:get'])]
    private ?string $absentBy = null;

    #[ORM\Column(name: 'TE_CREATED_AT')]
    #[Groups(['training_enrollment:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?string { return $this->id; }
    public function getEmployee(): ?string { return $this->employee; }
    public function setEmployee(string $employee): static { $this->employee = $employee; return $this; }
    public function getTrainingSession(): ?string { return $this->trainingSession; }
    public function setTrainingSession(string $trainingSession): static { $this->trainingSession = $trainingSession; return $this; }
    public function getStatus(): ?string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }
    public function getScore(): ?string { return $this->score; }
    public function setScore(?string $score): static { $this->score = $score; return $this; }
    public function getCertificate(): ?string { return $this->certificate; }
    public function setCertificate(?string $certificate): static { $this->certificate = $certificate; return $this; }
    public function getAssignedAt(): ?\DateTimeImmutable { return $this->assignedAt; }
    public function setAssignedAt(?\DateTimeImmutable $assignedAt): static { $this->assignedAt = $assignedAt; return $this; }
    public function getAssignedBy(): ?string { return $this->assignedBy; }
    public function setAssignedBy(?string $assignedBy): static { $this->assignedBy = $assignedBy; return $this; }
    public function getStartedAt(): ?\DateTimeImmutable { return $this->startedAt; }
    public function setStartedAt(?\DateTimeImmutable $startedAt): static { $this->startedAt = $startedAt; return $this; }
    public function getStartedBy(): ?string { return $this->startedBy; }
    public function setStartedBy(?string $startedBy): static { $this->startedBy = $startedBy; return $this; }
    public function getCompletedAt(): ?\DateTimeImmutable { return $this->completedAt; }
    public function setCompletedAt(?\DateTimeImmutable $completedAt): static { $this->completedAt = $completedAt; return $this; }
    public function getCompletedBy(): ?string { return $this->completedBy; }
    public function setCompletedBy(?string $completedBy): static { $this->completedBy = $completedBy; return $this; }
    public function getCertifiedAt(): ?\DateTimeImmutable { return $this->certifiedAt; }
    public function setCertifiedAt(?\DateTimeImmutable $certifiedAt): static { $this->certifiedAt = $certifiedAt; return $this; }
    public function getCertifiedBy(): ?string { return $this->certifiedBy; }
    public function setCertifiedBy(?string $certifiedBy): static { $this->certifiedBy = $certifiedBy; return $this; }
    public function getAbsentAt(): ?\DateTimeImmutable { return $this->absentAt; }
    public function setAbsentAt(?\DateTimeImmutable $absentAt): static { $this->absentAt = $absentAt; return $this; }
    public function getAbsentBy(): ?string { return $this->absentBy; }
    public function setAbsentBy(?string $absentBy): static { $this->absentBy = $absentBy; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }

    #[ORM\PrePersist]
    public function initCreatedAt(): void { $this->createdAt = new \DateTimeImmutable(); }
}
