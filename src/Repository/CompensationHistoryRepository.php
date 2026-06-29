<?php

namespace App\Repository;

use App\Entity\Contract;
use App\Entity\CompensationHistory;
use App\Model\ContractConstants;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CompensationHistory>
 */
class CompensationHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompensationHistory::class);
    }
}
