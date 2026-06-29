<?php

namespace App\Manager;

use App\Entity\JobFamily;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Repository\JobFamilyRepository;
use Doctrine\ORM\EntityManagerInterface;

class JobFamilyManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private JobFamilyRepository $repository,
    ) {
    }

    public function find(string $id): JobFamily
    {
        $jobFamily = $this->em->find(JobFamily::class, $id);

        if (null === $jobFamily) {
            throw new UnavailableDataException(sprintf('cannot find job family with id: %s', $id));
        }

        return $jobFamily;
    }

    public function findByCode(string $code): ?JobFamily
    {
        return $this->repository->findOneByCode($code);
    }

    public function assertCodeAvailable(string $code, ?string $excludeId = null): void
    {
        $existing = $this->repository->findOneByCode($code);

        if (null !== $existing && $existing->getId() !== $excludeId) {
            throw new InvalidActionInputException(sprintf('job family code already exists: %s', $code));
        }
    }
}
