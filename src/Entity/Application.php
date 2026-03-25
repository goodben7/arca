<?php

namespace App\Entity;

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
use App\Dto\HireApplicationDto;
use App\Dto\CreateApplicationDto;
use App\Dto\RejectApplicationDto;
use App\Dto\SetApplicationAppliedDto;
use App\Dto\SetApplicationInterviewDto;
use App\Dto\ShortlistApplicationDto;
use App\Model\ApplicationConstants;
use App\Model\RessourceInterface;
use App\Repository\ApplicationRepository;
use App\State\CreateApplicationProcessor;
use App\State\HireApplicationProcessor;
use App\State\RejectApplicationProcessor;
use App\State\SetApplicationAppliedProcessor;
use App\State\SetApplicationInterviewProcessor;
use App\State\ShortlistApplicationProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ApplicationRepository::class)]
#[ORM\Table(name: '`application`')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'application:get'],
    operations: [
        new Get(
            //security: 'is_granted("ROLE_APPLICATION_DETAILS")',
            provider: ItemProvider::class
        ),
        new GetCollection(
            //security: 'is_granted("ROLE_APPLICATION_LIST")',
            provider: CollectionProvider::class
        ),
        new Post(
            //security: 'is_granted("ROLE_APPLICATION_CREATE")',
            input: CreateApplicationDto::class,
            processor: CreateApplicationProcessor::class
        ),
        new Post(
            uriTemplate: '/applications/applied',
            security: 'is_granted("ROLE_APPLICATION_SET_APPLIED")',
            input: SetApplicationAppliedDto::class,
            processor: SetApplicationAppliedProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/applications/shortlistings',
            security: 'is_granted("ROLE_APPLICATION_SHORTLIST")',
            input: ShortlistApplicationDto::class,
            processor: ShortlistApplicationProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/applications/interviews',
            security: 'is_granted("ROLE_APPLICATION_INTERVIEW")',
            input: SetApplicationInterviewDto::class,
            processor: SetApplicationInterviewProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/applications/rejections',
            security: 'is_granted("ROLE_APPLICATION_REJECT")',
            input: RejectApplicationDto::class,
            processor: RejectApplicationProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/applications/hirings',
            security: 'is_granted("ROLE_APPLICATION_HIRE")',
            input: HireApplicationDto::class,
            processor: HireApplicationProcessor::class,
            status: 200
        ),
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'email' => 'ipartial',
    'phone' => 'ipartial',
    'jobOffer' => 'exact',
    'status' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'appliedAt'])]
class Application implements RessourceInterface
{
    public const string ID_PREFIX = 'AP';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'AP_ID', length: 16)]
    #[Groups(['application:get'])]
    private ?string $id = null;

    #[ORM\Column(name: 'AP_FIRST_NAME', length: 120)]
    #[Groups(['application:get'])]
    #[Assert\NotBlank]
    private ?string $firstName = null;

    #[ORM\Column(name: 'AP_LAST_NAME', length: 120)]
    #[Groups(['application:get'])]
    #[Assert\NotBlank]
    private ?string $lastName = null;

    #[ORM\Column(name: 'AP_GENDER', length: 1, nullable: true)]
    #[Groups(['application:get'])]
    private ?string $gender = null;

    #[ORM\Column(name: 'AP_EMAIL', length: 180)]
    #[Groups(['application:get'])]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(name: 'AP_PHONE', length: 40)]
    #[Groups(['application:get'])]
    #[Assert\NotBlank]
    private ?string $phone = null;

    #[ORM\Column(name: 'AP_JOB_OFFER', length: 16)]
    #[Groups(['application:get'])]
    #[Assert\NotBlank]
    private ?string $jobOffer = null;

    #[ORM\Column(name: 'AP_STATUS', length: 15)]
    #[Groups(['application:get'])]
    #[Assert\Choice(callback: [ApplicationConstants::class, 'getStatuses'])]
    #[Assert\NotBlank]
    private ?string $status = null;

    #[ORM\Column(name: 'AP_APPLIED_AT', type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['application:get'])]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $appliedAt = null;

    #[ORM\Column(name: 'AP_SHORTLISTED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['application:get'])]
    private ?\DateTimeImmutable $shortlistedAt = null;

    #[ORM\Column(name: 'AP_SHORTLISTED_BY', length: 16, nullable: true)]
    #[Groups(['application:get'])]
    private ?string $shortlistedBy = null;

    #[ORM\Column(name: 'AP_INTERVIEW_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['application:get'])]
    private ?\DateTimeImmutable $interviewAt = null;

    #[ORM\Column(name: 'AP_INTERVIEW_BY', length: 16, nullable: true)]
    #[Groups(['application:get'])]
    private ?string $interviewBy = null;

    #[ORM\Column(name: 'AP_REJECTED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['application:get'])]
    private ?\DateTimeImmutable $rejectedAt = null;

    #[ORM\Column(name: 'AP_REJECTED_BY', length: 16, nullable: true)]
    #[Groups(['application:get'])]
    private ?string $rejectedBy = null;

    #[ORM\Column(name: 'AP_REJECTION_REASON', type: Types::TEXT, nullable: true)]
    #[Groups(['application:get'])]
    private ?string $rejectionReason = null;

    #[ORM\Column(name: 'AP_HIRED_AT', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['application:get'])]
    private ?\DateTimeImmutable $hiredAt = null;

    #[ORM\Column(name: 'AP_HIRED_BY', length: 16, nullable: true)]
    #[Groups(['application:get'])]
    private ?string $hiredBy = null;

    #[ORM\Column(name: 'AP_NOTES', type: Types::TEXT, nullable: true)]
    #[Groups(['application:get'])]
    private ?string $notes = null;

    #[ORM\Column(name: 'AP_CREATED_AT')]
    #[Groups(['application:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?string { return $this->id; }
    public function getFirstName(): ?string { return $this->firstName; }
    public function setFirstName(string $firstName): static { $this->firstName = $firstName; return $this; }
    public function getLastName(): ?string { return $this->lastName; }
    public function setLastName(string $lastName): static { $this->lastName = $lastName; return $this; }
    public function getGender(): ?string { return $this->gender; }
    public function setGender(?string $gender): static { $this->gender = $gender; return $this; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }
    public function getPhone(): ?string { return $this->phone; }
    public function setPhone(string $phone): static { $this->phone = $phone; return $this; }
    public function getJobOffer(): ?string { return $this->jobOffer; }
    public function setJobOffer(string $jobOffer): static { $this->jobOffer = $jobOffer; return $this; }
    public function getStatus(): ?string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }
    public function getAppliedAt(): ?\DateTimeImmutable { return $this->appliedAt; }
    public function setAppliedAt(\DateTimeImmutable $appliedAt): static { $this->appliedAt = $appliedAt; return $this; }
    public function getShortlistedAt(): ?\DateTimeImmutable { return $this->shortlistedAt; }
    public function setShortlistedAt(?\DateTimeImmutable $shortlistedAt): static { $this->shortlistedAt = $shortlistedAt; return $this; }
    public function getShortlistedBy(): ?string { return $this->shortlistedBy; }
    public function setShortlistedBy(?string $shortlistedBy): static { $this->shortlistedBy = $shortlistedBy; return $this; }
    public function getInterviewAt(): ?\DateTimeImmutable { return $this->interviewAt; }
    public function setInterviewAt(?\DateTimeImmutable $interviewAt): static { $this->interviewAt = $interviewAt; return $this; }
    public function getInterviewBy(): ?string { return $this->interviewBy; }
    public function setInterviewBy(?string $interviewBy): static { $this->interviewBy = $interviewBy; return $this; }
    public function getRejectedAt(): ?\DateTimeImmutable { return $this->rejectedAt; }
    public function setRejectedAt(?\DateTimeImmutable $rejectedAt): static { $this->rejectedAt = $rejectedAt; return $this; }
    public function getRejectedBy(): ?string { return $this->rejectedBy; }
    public function setRejectedBy(?string $rejectedBy): static { $this->rejectedBy = $rejectedBy; return $this; }
    public function getRejectionReason(): ?string { return $this->rejectionReason; }
    public function setRejectionReason(?string $rejectionReason): static { $this->rejectionReason = $rejectionReason; return $this; }
    public function getHiredAt(): ?\DateTimeImmutable { return $this->hiredAt; }
    public function setHiredAt(?\DateTimeImmutable $hiredAt): static { $this->hiredAt = $hiredAt; return $this; }
    public function getHiredBy(): ?string { return $this->hiredBy; }
    public function setHiredBy(?string $hiredBy): static { $this->hiredBy = $hiredBy; return $this; }
    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $notes): static { $this->notes = $notes; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }

    #[ORM\PrePersist]
    public function initCreatedAt(): void { $this->createdAt = new \DateTimeImmutable(); }
}
