<?php

namespace App\Repository;

use App\Entity\SanctionScale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SanctionScale>
 */
class SanctionScaleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SanctionScale::class);
    }

    public function findOneByCode(string $code): ?SanctionScale
    {
        return $this->findOneBy(['code' => $code]);
    }
}
