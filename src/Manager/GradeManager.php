<?php

namespace App\Manager;

use App\Entity\Grade;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Model\GradeConstants;
use App\Repository\GradeRepository;
use Doctrine\ORM\EntityManagerInterface;

class GradeManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private GradeRepository $repository,
    ) {
    }

    public function find(string $id): Grade
    {
        $grade = $this->em->find(Grade::class, $id);

        if (null === $grade) {
            throw new UnavailableDataException(sprintf('cannot find grade with id: %s', $id));
        }

        return $grade;
    }

    public function findByCode(string $code): ?Grade
    {
        return $this->repository->findOneByCode($code);
    }

    public function assertCodeAvailable(string $code, ?string $excludeId = null): void
    {
        $existing = $this->repository->findOneByCode($code);

        if (null !== $existing && $existing->getId() !== $excludeId) {
            throw new InvalidActionInputException(sprintf('grade code already exists: %s', $code));
        }
    }

    public function assertRankValid(int $rank): void
    {
        if ($rank < GradeConstants::MIN_RANK || $rank > GradeConstants::MAX_RANK) {
            throw new InvalidActionInputException(sprintf(
                'grade rank must be between %d and %d',
                GradeConstants::MIN_RANK,
                GradeConstants::MAX_RANK,
            ));
        }
    }
}
