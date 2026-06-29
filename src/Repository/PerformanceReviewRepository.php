<?php

namespace App\Repository;

use App\Entity\Employee;
use App\Entity\PerformanceReview;
use App\Model\PerformanceReviewConstants;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PerformanceReview>
 */
class PerformanceReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PerformanceReview::class);
    }

    public function findLatestValidatedForEmployee(Employee|string $employee): ?PerformanceReview
    {
        $employeeId = $employee instanceof Employee ? $employee->getId() : $employee;

        return $this->createQueryBuilder('pr')
            ->andWhere('pr.employee = :employeeId')
            ->andWhere('pr.status = :status')
            ->setParameter('employeeId', $employeeId)
            ->setParameter('status', PerformanceReviewConstants::STATUS_VALIDATED)
            ->orderBy('pr.validatedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
