<?php

namespace App\Service\HrDashboard;

use App\Entity\Employee;
use App\Entity\MobilityRequest;
use App\Entity\TrainingEnrollment;
use App\Model\EmployeeConstants;
use App\Model\MobilityRequestConstants;
use App\Model\SkillConstants;
use App\Model\TrainingEnrollmentConstants;
use App\Repository\JobRoleRequiredSkillRepository;
use App\Repository\SuccessionPlanRepository;
use App\Service\CriticalJobRolesProvider;
use Doctrine\ORM\EntityManagerInterface;

class HrDashboardCalculator
{
    private const int PERIOD_MONTHS = 12;

    public function __construct(
        private EntityManagerInterface $em,
        private CriticalJobRolesProvider $criticalJobRoles,
        private SuccessionPlanRepository $successionPlans,
        private JobRoleRequiredSkillRepository $requiredSkills,
    ) {
    }

    public function compute(): array
    {
        $now = new \DateTimeImmutable();
        $periodStart = $now->modify(sprintf('-%d months', self::PERIOD_MONTHS));

        $headcount = $this->countEmployeesByStatus(EmployeeConstants::STATUS_ACTIVE);
        $departures = $this->countDeparturesSince($periodStart);
        $turnoverDenominator = max(1, $headcount + $departures);

        $criticalRoleIds = $this->criticalJobRoles->getCriticalJobRoleIds();
        $criticalRolesTotal = \count($criticalRoleIds);
        $criticalRolesCovered = $this->successionPlans->countCoveredCriticalJobRoles($criticalRoleIds);

        return [
            'headcount' => $headcount,
            'departuresLast12Months' => $departures,
            'turnoverRatePercent' => round(($departures / $turnoverDenominator) * 100, 1),
            'promotionsLast12Months' => $this->countPromotionsSince($periodStart),
            'trainingsInProgress' => $this->countTrainingEnrollmentsByStatus(TrainingEnrollmentConstants::STATUS_IN_PROGRESS),
            'trainingsCertifiedLast12Months' => $this->countCertifiedTrainingsSince($periodStart),
            'criticalJobRolesTotal' => $criticalRolesTotal,
            'criticalJobRolesCovered' => $criticalRolesCovered,
            'successionCoveragePercent' => 0 === $criticalRolesTotal
                ? 0.0
                : round(($criticalRolesCovered / $criticalRolesTotal) * 100, 1),
            'criticalSkillGaps' => $this->countCriticalSkillGaps($criticalRoleIds),
            'periodMonths' => self::PERIOD_MONTHS,
            'computedAt' => $now,
        ];
    }

    private function countEmployeesByStatus(string $status): int
    {
        return (int) $this->em->createQueryBuilder()
            ->select('COUNT(e.id)')
            ->from(Employee::class, 'e')
            ->andWhere('e.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function countDeparturesSince(\DateTimeImmutable $since): int
    {
        return (int) $this->em->createQueryBuilder()
            ->select('COUNT(e.id)')
            ->from(Employee::class, 'e')
            ->andWhere('e.status IN (:statuses)')
            ->andWhere('e.departureDate >= :since')
            ->setParameter('statuses', [
                EmployeeConstants::STATUS_TERMINATED,
                EmployeeConstants::STATUS_RETIRED,
            ])
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function countPromotionsSince(\DateTimeImmutable $since): int
    {
        return (int) $this->em->createQueryBuilder()
            ->select('COUNT(mr.id)')
            ->from(MobilityRequest::class, 'mr')
            ->andWhere('mr.type = :type')
            ->andWhere('mr.status = :status')
            ->andWhere('mr.implementedAt >= :since')
            ->setParameter('type', MobilityRequestConstants::TYPE_PROMOTION)
            ->setParameter('status', MobilityRequestConstants::STATUS_IMPLEMENTED)
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function countTrainingEnrollmentsByStatus(string $status): int
    {
        return (int) $this->em->createQueryBuilder()
            ->select('COUNT(te.id)')
            ->from(TrainingEnrollment::class, 'te')
            ->andWhere('te.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function countCertifiedTrainingsSince(\DateTimeImmutable $since): int
    {
        return (int) $this->em->createQueryBuilder()
            ->select('COUNT(te.id)')
            ->from(TrainingEnrollment::class, 'te')
            ->andWhere('te.status = :status')
            ->andWhere('te.certifiedAt >= :since')
            ->setParameter('status', TrainingEnrollmentConstants::STATUS_CERTIFIED)
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param list<string> $criticalRoleIds
     */
    private function countCriticalSkillGaps(array $criticalRoleIds): int
    {
        if ([] === $criticalRoleIds) {
            return 0;
        }

        $employees = $this->em->createQueryBuilder()
            ->select('e')
            ->from(Employee::class, 'e')
            ->innerJoin('e.jobRole', 'jr')
            ->andWhere('jr.id IN (:roleIds)')
            ->andWhere('e.status = :status')
            ->setParameter('roleIds', $criticalRoleIds)
            ->setParameter('status', EmployeeConstants::STATUS_ACTIVE)
            ->getQuery()
            ->getResult();

        $gaps = 0;

        foreach ($employees as $employee) {
            if (!$employee instanceof Employee) {
                continue;
            }

            $jobRole = $employee->getJobRole();
            if (null === $jobRole) {
                continue;
            }

            foreach ($this->requiredSkills->findByJobRole($jobRole) as $requirement) {
                $skill = $requirement->getSkill();
                if (null === $skill || null === $requirement->getMinimumLevel()) {
                    continue;
                }

                $employeeSkill = $this->em->getRepository(\App\Entity\EmployeeSkill::class)->findOneBy([
                    'employee' => $employee->getId(),
                    'skill' => $skill,
                ]);

                if (null === $employeeSkill || null === $employeeSkill->getLevel()) {
                    ++$gaps;
                    continue;
                }

                if (SkillConstants::getLevelRank($employeeSkill->getLevel()) < SkillConstants::getLevelRank($requirement->getMinimumLevel())) {
                    ++$gaps;
                }
            }
        }

        return $gaps;
    }
}
