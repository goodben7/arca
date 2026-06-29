<?php

namespace App\Repository;

use App\Entity\SuccessionPlan;
use App\Model\SuccessionPlanConstants;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SuccessionPlan>
 */
class SuccessionPlanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SuccessionPlan::class);
    }

    /**
     * @param list<string> $criticalJobRoleIds
     */
    public function countCoveredCriticalJobRoles(array $criticalJobRoleIds): int
    {
        if ([] === $criticalJobRoleIds) {
            return 0;
        }

        return (int) $this->createQueryBuilder('sp')
            ->select('COUNT(DISTINCT jr.id)')
            ->innerJoin('sp.criticalJobRole', 'jr')
            ->andWhere('jr.id IN (:roleIds)')
            ->andWhere('sp.status = :status')
            ->setParameter('roleIds', $criticalJobRoleIds)
            ->setParameter('status', SuccessionPlanConstants::STATUS_ACTIVE)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findActiveByCriticalJobRoleAndCandidate(string $criticalJobRoleId, string $candidateId): ?SuccessionPlan
    {
        return $this->createQueryBuilder('sp')
            ->innerJoin('sp.criticalJobRole', 'jr')
            ->andWhere('jr.id = :roleId')
            ->andWhere('sp.candidate = :candidate')
            ->andWhere('sp.status = :status')
            ->setParameter('roleId', $criticalJobRoleId)
            ->setParameter('candidate', $candidateId)
            ->setParameter('status', SuccessionPlanConstants::STATUS_ACTIVE)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
