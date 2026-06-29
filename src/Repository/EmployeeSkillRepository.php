<?php

namespace App\Repository;

use App\Entity\EmployeeSkill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmployeeSkill>
 */
class EmployeeSkillRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmployeeSkill::class);
    }

    public function findOneByEmployeeAndSkill(string $employeeId, string $skillId): ?EmployeeSkill
    {
        return $this->createQueryBuilder('es')
            ->innerJoin('es.skill', 'skill')
            ->andWhere('es.employee = :employeeId')
            ->andWhere('skill.id = :skillId')
            ->setParameter('employeeId', $employeeId)
            ->setParameter('skillId', $skillId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
