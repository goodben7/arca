<?php

namespace App\Repository;

use App\Entity\ExitProcess;
use App\Entity\ExitTask;
use App\Model\ExitTaskConstants;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExitTask>
 */
class ExitTaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExitTask::class);
    }

    public function countOpenByProcess(ExitProcess $process): int
    {
        return (int) $this->createQueryBuilder('xt')
            ->select('COUNT(xt.id)')
            ->innerJoin('xt.process', 'process')
            ->andWhere('process.id = :processId')
            ->andWhere('xt.status IN (:statuses)')
            ->setParameter('processId', $process->getId())
            ->setParameter('statuses', [
                ExitTaskConstants::STATUS_PENDING,
                ExitTaskConstants::STATUS_IN_PROGRESS,
            ])
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countCompletedByProcess(ExitProcess $process): int
    {
        return (int) $this->createQueryBuilder('xt')
            ->select('COUNT(xt.id)')
            ->innerJoin('xt.process', 'process')
            ->andWhere('process.id = :processId')
            ->andWhere('xt.status = :status')
            ->setParameter('processId', $process->getId())
            ->setParameter('status', ExitTaskConstants::STATUS_COMPLETED)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
