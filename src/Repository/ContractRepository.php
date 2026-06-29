<?php

namespace App\Repository;

use App\Entity\Contract;
use App\Model\ContractConstants;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ContractRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contract::class);
    }

    public function findActiveByEmployee(string $employeeId): ?Contract
    {
        return $this->createQueryBuilder('c')
            ->where('c.employee = :employee')
            ->andWhere('c.status = :status')
            ->setParameter('employee', $employeeId)
            ->setParameter('status', ContractConstants::STATUS_ACTIVE)
            ->orderBy('c.startDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
} 
