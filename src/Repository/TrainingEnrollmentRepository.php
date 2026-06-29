<?php

namespace App\Repository;

use App\Entity\TrainingEnrollment;
use App\Entity\TrainingSession;
use App\Model\TrainingEnrollmentConstants;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TrainingEnrollmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TrainingEnrollment::class);
    }

    public function hasCertifiedCatalogForEmployee(string $employeeId, string $catalogId): bool
    {
        $count = (int) $this->createQueryBuilder('te')
            ->select('COUNT(te.id)')
            ->innerJoin(TrainingSession::class, 'ts', 'WITH', 'ts.id = te.trainingSession')
            ->andWhere('te.employee = :employeeId')
            ->andWhere('te.status = :status')
            ->andWhere('IDENTITY(ts.catalogItem) = :catalogId')
            ->setParameter('employeeId', $employeeId)
            ->setParameter('status', TrainingEnrollmentConstants::STATUS_CERTIFIED)
            ->setParameter('catalogId', $catalogId)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }
}
