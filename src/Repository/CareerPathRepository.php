<?php

namespace App\Repository;

use App\Entity\CareerPath;
use App\Entity\JobRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CareerPath>
 */
class CareerPathRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CareerPath::class);
    }

    public function findOneByTransition(JobRole|string $fromJobRole, JobRole|string $toJobRole): ?CareerPath
    {
        $fromId = $fromJobRole instanceof JobRole ? $fromJobRole->getId() : $fromJobRole;
        $toId = $toJobRole instanceof JobRole ? $toJobRole->getId() : $toJobRole;

        return $this->createQueryBuilder('cp')
            ->innerJoin('cp.fromJobRole', 'fromRole')
            ->innerJoin('cp.toJobRole', 'toRole')
            ->andWhere('fromRole.id = :fromId')
            ->andWhere('toRole.id = :toId')
            ->setParameter('fromId', $fromId)
            ->setParameter('toId', $toId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
