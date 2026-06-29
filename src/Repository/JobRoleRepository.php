<?php

namespace App\Repository;

use App\Entity\JobRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JobRole>
 */
class JobRoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JobRole::class);
    }

    public function findOneByCode(string $code): ?JobRole
    {
        return $this->findOneBy(['code' => $code]);
    }
}
