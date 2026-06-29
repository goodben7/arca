<?php

namespace App\Manager;

use App\Entity\JobFamily;
use App\Entity\Grade;
use App\Entity\JobRole;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Repository\JobRoleRepository;
use Doctrine\ORM\EntityManagerInterface;

class JobRoleManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private JobRoleRepository $repository,
        private JobFamilyManager $jobFamilyManager,
        private GradeManager $gradeManager,
    ) {
    }

    public function find(string $id): JobRole
    {
        $jobRole = $this->em->find(JobRole::class, $id);

        if (null === $jobRole) {
            throw new UnavailableDataException(sprintf('cannot find job role with id: %s', $id));
        }

        return $jobRole;
    }

    public function findByCode(string $code): ?JobRole
    {
        return $this->repository->findOneByCode($code);
    }

    public function resolveJobRole(JobRole|string $jobRole): JobRole
    {
        if ($jobRole instanceof JobRole) {
            return $jobRole;
        }

        return $this->find($jobRole);
    }

    public function assertCodeAvailable(string $code, ?string $excludeId = null): void
    {
        $existing = $this->repository->findOneByCode($code);

        if (null !== $existing && $existing->getId() !== $excludeId) {
            throw new InvalidActionInputException(sprintf('job role code already exists: %s', $code));
        }
    }

    public function resolveJobFamily(JobFamily|string $jobFamily): JobFamily
    {
        if ($jobFamily instanceof JobFamily) {
            return $jobFamily;
        }

        return $this->jobFamilyManager->find($jobFamily);
    }

    public function resolveGrade(Grade|string $grade): Grade
    {
        if ($grade instanceof Grade) {
            return $grade;
        }

        return $this->gradeManager->find($grade);
    }
}
