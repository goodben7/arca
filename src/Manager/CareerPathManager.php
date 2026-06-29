<?php

namespace App\Manager;

use App\Entity\CareerPath;
use App\Entity\JobRole;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Repository\CareerPathRepository;
use Doctrine\ORM\EntityManagerInterface;

class CareerPathManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private CareerPathRepository $repository,
        private JobRoleManager $jobRoleManager,
    ) {
    }

    public function find(string $id): CareerPath
    {
        $careerPath = $this->em->find(CareerPath::class, $id);

        if (null === $careerPath) {
            throw new UnavailableDataException(sprintf('cannot find career path with id: %s', $id));
        }

        return $careerPath;
    }

    public function findByTransition(JobRole|string $fromJobRole, JobRole|string $toJobRole): ?CareerPath
    {
        return $this->repository->findOneByTransition($fromJobRole, $toJobRole);
    }

    public function assertValidTransition(JobRole $fromJobRole, JobRole $toJobRole, ?string $excludeId = null): void
    {
        if ($fromJobRole->getId() === $toJobRole->getId()) {
            throw new InvalidActionInputException('career path fromJobRole and toJobRole must be different');
        }

        $existing = $this->repository->findOneByTransition($fromJobRole, $toJobRole);

        if (null !== $existing && $existing->getId() !== $excludeId) {
            throw new InvalidActionInputException('career path already exists for this transition');
        }
    }

    public function resolveJobRole(JobRole|string $jobRole): JobRole
    {
        return $this->jobRoleManager->resolveJobRole($jobRole);
    }
}
