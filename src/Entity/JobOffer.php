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
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use App\Doctrine\IdGenerator;
use App\Dto\CloseJobOfferDto;
use App\Dto\CreateJobOfferDto;
use App\Dto\PublishJobOfferDto;
use App\Dto\SetJobOfferDraftDto;
use App\Model\JobOfferConstants;
use App\Model\RessourceInterface;
use App\Repository\JobOfferRepository;
use App\State\CloseJobOfferProcessor;
use App\State\CreateJobOfferProcessor;
use App\State\PublishJobOfferProcessor;
use App\State\SetJobOfferDraftProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: JobOfferRepository::class)]
#[ORM\Table(name: '`job_offer`')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'job_offer:get'],
    operations: [
        new Get(
            //security: 'is_granted("ROLE_JOB_OFFER_DETAILS")',
            provider: ItemProvider::class
        ),
        new GetCollection(
            //security: 'is_granted("ROLE_JOB_OFFER_LIST")',
            provider: CollectionProvider::class
        ),
        new Post(
            security: 'is_granted("ROLE_JOB_OFFER_CREATE")',
            input: CreateJobOfferDto::class,
            processor: CreateJobOfferProcessor::class 
        ),
        new Patch(
            security: 'is_granted("ROLE_JOB_OFFER_UPDATE")',
            denormalizationContext: ['groups' => 'job_offer:patch',],
            processor: PersistProcessor::class,
        ),
        new Post(
            uriTemplate: '/job_offers/publications',
            security: 'is_granted("ROLE_JOB_OFFER_PUBLISH")',
            input: PublishJobOfferDto::class,
            processor: PublishJobOfferProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/job_offers/closures',
            security: 'is_granted("ROLE_JOB_OFFER_CLOSE")',
            input: CloseJobOfferDto::class,
            processor: CloseJobOfferProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/job_offers/drafts',
            security: 'is_granted("ROLE_JOB_OFFER_SET_DRAFT")',
            input: SetJobOfferDraftDto::class,
            processor: SetJobOfferDraftProcessor::class,
            status: 200
        ),
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'department' => 'exact',
    'recruitmentRequest' => 'exact',
    'status' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt'])]
class JobOffer implements RessourceInterface
{
    public const string ID_PREFIX = 'JO';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'JO_ID', length: 16)]
    #[Groups(['job_offer:get'])]
    private ?string $id = null;

    #[ORM\Column(name: 'JO_TITLE', length: 120)]
    #[Groups(['job_offer:get', 'job_offer:patch'])]
    #[Assert\NotBlank]
    private ?string $title = null;

    #[ORM\Column(name: 'JO_DEPARTMENT', length: 16)]
    #[Groups(['job_offer:get'])]
    #[Assert\NotBlank]
    private ?string $department = null;

    #[ORM\Column(name: 'JO_RECRUITMENT_REQUEST', length: 16)]
    #[Groups(['job_offer:get'])]
    #[Assert\NotBlank]
    private ?string $recruitmentRequest = null;

    #[ORM\Column(name: 'JO_STATUS', length: 15)]
    #[Groups(['job_offer:get'])]
    #[Assert\Choice(callback: [JobOfferConstants::class, 'getStatuses'])]
    #[Assert\NotBlank]
    private ?string $status = null;

    #[ORM\Column(name: 'JO_PUBLISHED_AT', nullable: true)]
    #[Groups(['job_offer:get'])]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column(name: 'JO_PUBLISHED_BY', length: 16, nullable: true)]
    #[Groups(['job_offer:get'])]
    private ?string $publishedBy = null;

    #[ORM\Column(name: 'JO_CLOSED_AT', nullable: true)]
    #[Groups(['job_offer:get'])]
    private ?\DateTimeImmutable $closedAt = null;

    #[ORM\Column(name: 'JO_CLOSED_BY', length: 16, nullable: true)]
    #[Groups(['job_offer:get'])]
    private ?string $closedBy = null;

    #[ORM\Column(name: 'JO_CREATED_AT')]
    #[Groups(['job_offer:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?string { return $this->id; }
    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }
    public function getDepartment(): ?string { return $this->department; }
    public function setDepartment(string $department): static { $this->department = $department; return $this; }
    public function getRecruitmentRequest(): ?string { return $this->recruitmentRequest; }
    public function setRecruitmentRequest(string $recruitmentRequest): static { $this->recruitmentRequest = $recruitmentRequest; return $this; }
    public function getStatus(): ?string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }
    public function getPublishedAt(): ?\DateTimeImmutable { return $this->publishedAt; }
    public function setPublishedAt(?\DateTimeImmutable $publishedAt): static { $this->publishedAt = $publishedAt; return $this; }
    public function getPublishedBy(): ?string { return $this->publishedBy; }
    public function setPublishedBy(?string $publishedBy): static { $this->publishedBy = $publishedBy; return $this; }
    public function getClosedAt(): ?\DateTimeImmutable { return $this->closedAt; }
    public function setClosedAt(?\DateTimeImmutable $closedAt): static { $this->closedAt = $closedAt; return $this; }
    public function getClosedBy(): ?string { return $this->closedBy; }
    public function setClosedBy(?string $closedBy): static { $this->closedBy = $closedBy; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }

    #[ORM\PrePersist]
    public function initCreatedAt(): void { $this->createdAt = new \DateTimeImmutable(); }
}
