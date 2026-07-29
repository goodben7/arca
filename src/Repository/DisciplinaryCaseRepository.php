<?php

namespace App\Repository;

use App\Entity\DisciplinaryCase;
use App\Model\DisciplinaryCaseConstants;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DisciplinaryCase>
 */
class DisciplinaryCaseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DisciplinaryCase::class);
    }

    public function findActiveForEmployee(string $employeeId): ?DisciplinaryCase
    {
        return $this->createQueryBuilder('dc')
            ->andWhere('dc.employee = :employeeId')
            ->andWhere('dc.status NOT IN (:terminal)')
            ->setParameter('employeeId', $employeeId)
            ->setParameter('terminal', DisciplinaryCaseConstants::getTerminalStatuses())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countAppliedSanctionsForEmployee(string $employeeId): int
    {
        return (int) $this->createQueryBuilder('dc')
            ->select('COUNT(dc.id)')
            ->andWhere('dc.employee = :employeeId')
            ->andWhere('dc.status IN (:appliedStatuses)')
            ->setParameter('employeeId', $employeeId)
            ->setParameter('appliedStatuses', [
                DisciplinaryCaseConstants::STATUS_SANCTION_APPLIED,
                DisciplinaryCaseConstants::STATUS_CLOSED,
            ])
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findLatestAppliedForEmployee(string $employeeId): ?DisciplinaryCase
    {
        return $this->createQueryBuilder('dc')
            ->andWhere('dc.employee = :employeeId')
            ->andWhere('dc.status IN (:appliedStatuses)')
            ->andWhere('dc.appliedAt IS NOT NULL')
            ->setParameter('employeeId', $employeeId)
            ->setParameter('appliedStatuses', [
                DisciplinaryCaseConstants::STATUS_SANCTION_APPLIED,
                DisciplinaryCaseConstants::STATUS_CLOSED,
            ])
            ->orderBy('dc.appliedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getMaxSeverityForEmployee(string $employeeId): ?int
    {
        $result = $this->createQueryBuilder('dc')
            ->select('MAX(ss.severityLevel)')
            ->innerJoin('dc.sanctionScale', 'ss')
            ->andWhere('dc.employee = :employeeId')
            ->andWhere('dc.status IN (:appliedStatuses)')
            ->setParameter('employeeId', $employeeId)
            ->setParameter('appliedStatuses', [
                DisciplinaryCaseConstants::STATUS_SANCTION_APPLIED,
                DisciplinaryCaseConstants::STATUS_CLOSED,
            ])
            ->getQuery()
            ->getSingleScalarResult();

        return null !== $result ? (int) $result : null;
    }
}
