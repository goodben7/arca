<?php

namespace App\Repository;

use App\Entity\OnboardingProcess;
use App\Entity\OnboardingTask;
use App\Model\OnboardingTaskConstants;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OnboardingTask>
 */
class OnboardingTaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OnboardingTask::class);
    }

    /**
     * @return list<OnboardingTask>
     */
    public function findByProcess(OnboardingProcess|string $process): array
    {
        $processId = $process instanceof OnboardingProcess ? $process->getId() : $process;

        return $this->createQueryBuilder('ot')
            ->innerJoin('ot.process', 'process')
            ->andWhere('process.id = :processId')
            ->setParameter('processId', $processId)
            ->getQuery()
            ->getResult();
    }

    public function countOpenByProcess(OnboardingProcess $process): int
    {
        return (int) $this->createQueryBuilder('ot')
            ->select('COUNT(ot.id)')
            ->innerJoin('ot.process', 'process')
            ->andWhere('process.id = :processId')
            ->andWhere('ot.status IN (:statuses)')
            ->setParameter('processId', $process->getId())
            ->setParameter('statuses', [
                OnboardingTaskConstants::STATUS_PENDING,
                OnboardingTaskConstants::STATUS_IN_PROGRESS,
            ])
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countCompletedByProcess(OnboardingProcess $process): int
    {
        return (int) $this->createQueryBuilder('ot')
            ->select('COUNT(ot.id)')
            ->innerJoin('ot.process', 'process')
            ->andWhere('process.id = :processId')
            ->andWhere('ot.status = :status')
            ->setParameter('processId', $process->getId())
            ->setParameter('status', OnboardingTaskConstants::STATUS_COMPLETED)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
