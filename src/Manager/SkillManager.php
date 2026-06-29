<?php

namespace App\Manager;

use App\Entity\Skill;
use App\Entity\SkillCategory;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Repository\SkillRepository;
use Doctrine\ORM\EntityManagerInterface;

class SkillManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private SkillRepository $repository,
        private SkillCategoryManager $categories,
    ) {
    }

    public function find(string $id): Skill
    {
        $skill = $this->em->find(Skill::class, $id);

        if (null === $skill) {
            throw new UnavailableDataException(sprintf('cannot find skill with id: %s', $id));
        }

        return $skill;
    }

    public function findByCode(string $code): ?Skill
    {
        return $this->repository->findOneByCode($code);
    }

    public function assertCodeAvailable(string $code, ?string $excludeId = null): void
    {
        $existing = $this->repository->findOneByCode($code);

        if (null !== $existing && $existing->getId() !== $excludeId) {
            throw new InvalidActionInputException(sprintf('skill code already exists: %s', $code));
        }
    }

    public function resolveCategory(SkillCategory|string $category): SkillCategory
    {
        if ($category instanceof SkillCategory) {
            return $category;
        }

        return $this->categories->find($category);
    }
}
