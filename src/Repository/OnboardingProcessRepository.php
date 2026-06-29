<?php

namespace App\Repository;

use App\Entity\OnboardingProcess;
use App\Entity\OnboardingTask;
use App\Model\OnboardingProcessConstants;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OnboardingProcess>
 */
class OnboardingProcessRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OnboardingProcess::class);
    }

    public function findActiveForEmployee(string $employeeId): ?OnboardingProcess
    {
        return $this->createQueryBuilder('op')
            ->andWhere('op.employee = :employeeId')
            ->andWhere('op.status IN (:statuses)')
            ->setParameter('employeeId', $employeeId)
            ->setParameter('statuses', [
                OnboardingProcessConstants::STATUS_PENDING,
                OnboardingProcessConstants::STATUS_IN_PROGRESS,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
