<?php

namespace App\Repository;

use App\Entity\JobRole;
use App\Entity\JobRoleRequiredTraining;
use App\Entity\TrainingCatalog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JobRoleRequiredTraining>
 */
class JobRoleRequiredTrainingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JobRoleRequiredTraining::class);
    }

    /**
     * @return list<JobRoleRequiredTraining>
     */
    public function findByJobRole(JobRole|string $jobRole): array
    {
        $jobRoleId = $jobRole instanceof JobRole ? $jobRole->getId() : $jobRole;

        return $this->createQueryBuilder('jrt')
            ->innerJoin('jrt.jobRole', 'jobRole')
            ->andWhere('jobRole.id = :jobRoleId')
            ->setParameter('jobRoleId', $jobRoleId)
            ->getQuery()
            ->getResult();
    }

    public function findOneByJobRoleAndCatalog(JobRole|string $jobRole, TrainingCatalog|string $catalog): ?JobRoleRequiredTraining
    {
        $jobRoleId = $jobRole instanceof JobRole ? $jobRole->getId() : $jobRole;
        $catalogId = $catalog instanceof TrainingCatalog ? $catalog->getId() : $catalog;

        return $this->createQueryBuilder('jrt')
            ->innerJoin('jrt.jobRole', 'jobRole')
            ->innerJoin('jrt.catalogItem', 'catalogItem')
            ->andWhere('jobRole.id = :jobRoleId')
            ->andWhere('catalogItem.id = :catalogId')
            ->setParameter('jobRoleId', $jobRoleId)
            ->setParameter('catalogId', $catalogId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
