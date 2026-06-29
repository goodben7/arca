<?php

namespace App\Manager;

use App\Entity\JobRole;
use App\Entity\JobRoleRequiredSkill;
use App\Entity\Skill;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Repository\JobRoleRequiredSkillRepository;
use Doctrine\ORM\EntityManagerInterface;

class JobRoleRequiredSkillManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private JobRoleRequiredSkillRepository $repository,
        private JobRoleManager $jobRoles,
        private SkillManager $skills,
    ) {
    }

    public function find(string $id): JobRoleRequiredSkill
    {
        $requiredSkill = $this->em->find(JobRoleRequiredSkill::class, $id);

        if (null === $requiredSkill) {
            throw new UnavailableDataException(sprintf('cannot find job role required skill with id: %s', $id));
        }

        return $requiredSkill;
    }

    public function assertValidRequirement(JobRole $jobRole, Skill $skill, ?string $excludeId = null): void
    {
        $existing = $this->repository->findOneByJobRoleAndSkill($jobRole, $skill);

        if (null !== $existing && $existing->getId() !== $excludeId) {
            throw new InvalidActionInputException('required skill already exists for this job role');
        }
    }

    public function resolveJobRole(JobRole|string $jobRole): JobRole
    {
        return $this->jobRoles->resolveJobRole($jobRole);
    }

    public function resolveSkill(Skill|string $skill): Skill
    {
        return $this->skills->find($skill instanceof Skill ? $skill->getId() : $skill);
    }
}
