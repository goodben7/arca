<?php

namespace App\Manager;

use App\Entity\SkillCategory;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Repository\SkillCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;

class SkillCategoryManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private SkillCategoryRepository $repository,
    ) {
    }

    public function find(string $id): SkillCategory
    {
        $category = $this->em->find(SkillCategory::class, $id);

        if (null === $category) {
            throw new UnavailableDataException(sprintf('cannot find skill category with id: %s', $id));
        }

        return $category;
    }

    public function findByCode(string $code): ?SkillCategory
    {
        return $this->repository->findOneByCode($code);
    }

    public function assertCodeAvailable(string $code, ?string $excludeId = null): void
    {
        $existing = $this->repository->findOneByCode($code);

        if (null !== $existing && $existing->getId() !== $excludeId) {
            throw new InvalidActionInputException(sprintf('skill category code already exists: %s', $code));
        }
    }
}
