<?php

namespace App\Repository;

use App\Entity\ExitProcess;
use App\Model\ExitProcessConstants;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExitProcess>
 */
class ExitProcessRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExitProcess::class);
    }

    public function findActiveForEmployee(string $employeeId): ?ExitProcess
    {
        return $this->createQueryBuilder('ep')
            ->andWhere('ep.employee = :employeeId')
            ->andWhere('ep.status IN (:statuses)')
            ->setParameter('employeeId', $employeeId)
            ->setParameter('statuses', [
                ExitProcessConstants::STATUS_PENDING,
                ExitProcessConstants::STATUS_IN_PROGRESS,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
