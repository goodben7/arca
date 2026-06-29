<?php

namespace App\Repository;

use App\Entity\EmployeeJourneyEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmployeeJourneyEntry>
 */
class EmployeeJourneyEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmployeeJourneyEntry::class);
    }

    /**
     * @return list<EmployeeJourneyEntry>
     */
    public function findByEmployeeOrdered(string $employeeId): array
    {
        return $this->createQueryBuilder('j')
            ->innerJoin('j.employee', 'e')
            ->andWhere('e.id = :employeeId')
            ->setParameter('employeeId', $employeeId)
            ->orderBy('j.occurredAt', 'DESC')
            ->addOrderBy('j.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
