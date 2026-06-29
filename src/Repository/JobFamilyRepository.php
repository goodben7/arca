<?php

namespace App\Repository;

use App\Entity\JobFamily;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JobFamily>
 */
class JobFamilyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JobFamily::class);
    }

    public function findOneByCode(string $code): ?JobFamily
    {
        return $this->findOneBy(['code' => $code]);
    }
}
