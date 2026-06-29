<?php

namespace App\Repository;

use App\Entity\JobRole;
use App\Entity\JobRoleRequiredSkill;
use App\Entity\Skill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JobRoleRequiredSkill>
 */
class JobRoleRequiredSkillRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JobRoleRequiredSkill::class);
    }

    public function findOneByJobRoleAndSkill(JobRole|string $jobRole, Skill|string $skill): ?JobRoleRequiredSkill
    {
        $jobRoleId = $jobRole instanceof JobRole ? $jobRole->getId() : $jobRole;
        $skillId = $skill instanceof Skill ? $skill->getId() : $skill;

        return $this->createQueryBuilder('jrs')
            ->innerJoin('jrs.jobRole', 'jobRole')
            ->innerJoin('jrs.skill', 'skill')
            ->andWhere('jobRole.id = :jobRoleId')
            ->andWhere('skill.id = :skillId')
            ->setParameter('jobRoleId', $jobRoleId)
            ->setParameter('skillId', $skillId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return list<JobRoleRequiredSkill>
     */
    public function findByJobRole(JobRole|string $jobRole): array
    {
        $jobRoleId = $jobRole instanceof JobRole ? $jobRole->getId() : $jobRole;

        return $this->createQueryBuilder('jrs')
            ->innerJoin('jrs.jobRole', 'jobRole')
            ->andWhere('jobRole.id = :jobRoleId')
            ->setParameter('jobRoleId', $jobRoleId)
            ->getQuery()
            ->getResult();
    }
}
