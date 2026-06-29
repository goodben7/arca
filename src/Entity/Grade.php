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
use App\Model\GradeConstants;
use App\Model\RessourceInterface;
use App\Repository\GradeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: GradeRepository::class)]
#[ORM\Table(name: '`grade`')]
#[ORM\UniqueConstraint(name: 'UNIQ_GRADE_CODE', fields: ['code'])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'grade:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_GRADE_DETAILS")',
            provider: ItemProvider::class,
        ),
        new GetCollection(
            security: 'is_granted("ROLE_GRADE_LIST")',
            provider: CollectionProvider::class,
        ),
        new Post(
            security: 'is_granted("ROLE_GRADE_CREATE")',
            denormalizationContext: ['groups' => 'grade:post'],
            processor: PersistProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_GRADE_UPDATE")',
            denormalizationContext: ['groups' => 'grade:patch'],
            processor: PersistProcessor::class,
        ),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'code' => 'ipartial',
    'name' => 'ipartial',
    'rank' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'updatedAt', 'name', 'code', 'rank'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'updatedAt'])]
class Grade implements RessourceInterface
{
    public const string ID_PREFIX = 'GR';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(name: 'GR_ID', length: 16)]
    #[Groups(['grade:get', 'job_role:get', 'job_offer:get', 'recruitment_request:get', 'employee:get'])]
    private ?string $id = null;

    #[ORM\Column(name: 'GR_CODE', length: 40)]
    #[Groups(['grade:get', 'grade:post', 'grade:patch', 'job_role:get', 'employee:get'])]
    #[Assert\NotBlank]
    private ?string $code = null;

    #[ORM\Column(name: 'GR_NAME', length: 120)]
    #[Groups(['grade:get', 'grade:post', 'grade:patch', 'job_role:get', 'employee:get'])]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(name: 'GR_RANK')]
    #[Groups(['grade:get', 'grade:post', 'grade:patch', 'job_role:get', 'employee:get'])]
    #[Assert\NotNull]
    #[Assert\Range(min: GradeConstants::MIN_RANK, max: GradeConstants::MAX_RANK)]
    private ?int $rank = null;

    #[ORM\Column(name: 'GR_CREATED_AT')]
    #[Groups(['grade:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'GR_UPDATED_AT', nullable: true)]
    #[Groups(['grade:get'])]
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(int $rank): static
    {
        $this->rank = $rank;

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
