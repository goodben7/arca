<?php

namespace App\Policy;

use App\Entity\CareerPath;
use App\Entity\Employee;
use App\Entity\JobRole;
use App\Manager\CareerPathManager;
use App\Manager\JobRoleManager;
use App\Model\CareerPathConstants;
use App\Model\EligibilityActionConstants;
use App\Model\EmployeeConstants;
use App\Model\SkillConstants;
use App\Repository\EmployeeSkillRepository;
use App\Repository\JobRoleRequiredSkillRepository;
use App\Repository\PerformanceReviewRepository;

class PromotionEligibilityPolicy implements EligibilityPolicyInterface
{
    public function __construct(
        private CareerPathManager $careerPaths,
        private JobRoleManager $jobRoles,
        private PerformanceReviewRepository $performanceReviews,
        private JobRoleRequiredSkillRepository $requiredSkills,
        private EmployeeSkillRepository $employeeSkills,
    ) {
    }

    public function supports(string $action): bool
    {
        return EligibilityActionConstants::PROMOTION === $action;
    }

    public function evaluate(Employee $employee, array $context = []): PolicyResult
    {
        $reasons = [];
        $targetJobRoleId = $context['targetJobRoleId'] ?? null;

        if (EmployeeConstants::STATUS_ACTIVE !== $employee->getStatus()) {
            $reasons[] = 'employee must be active';
        }

        $currentJobRole = $employee->getJobRole();
        if (null === $currentJobRole) {
            $reasons[] = 'employee has no current job role';
        }

        if (!$targetJobRoleId) {
            $reasons[] = 'targetJobRoleId is required';

            return PolicyResult::notEligible($reasons);
        }

        $targetJobRole = $this->jobRoles->find($targetJobRoleId);

        if (null !== $currentJobRole) {
            $careerPath = $this->careerPaths->findByTransition($currentJobRole, $targetJobRole);
            if (null === $careerPath) {
                $reasons[] = 'no career path defined for this transition';
            } else {
                $this->evaluateCareerPathConditions($employee, $careerPath, $reasons);
            }
        }

        $this->evaluateRequiredSkills($employee, $targetJobRole, $reasons);

        return [] === $reasons
            ? PolicyResult::eligible()
            : PolicyResult::notEligible($reasons);
    }

    /**
     * @param list<string> $reasons
     */
    private function evaluateCareerPathConditions(Employee $employee, CareerPath $careerPath, array &$reasons): void
    {
        $conditions = $careerPath->getConditions() ?? [];
        $hireDate = $employee->getHireDate();

        if (null === $hireDate) {
            $reasons[] = 'employee hire date is required to evaluate tenure';

            return;
        }

        $now = new \DateTimeImmutable();
        $tenureMonths = ((int) $hireDate->diff($now)->format('%y')) * 12 + (int) $hireDate->diff($now)->format('%m');

        if (isset($conditions[CareerPathConstants::CONDITION_MINIMUM_YEARS])) {
            $requiredMonths = (int) $conditions[CareerPathConstants::CONDITION_MINIMUM_YEARS] * 12;
            if ($tenureMonths < $requiredMonths) {
                $reasons[] = sprintf('minimum tenure of %d year(s) not met', (int) $conditions[CareerPathConstants::CONDITION_MINIMUM_YEARS]);
            }
        }

        if (isset($conditions['minTenureMonths'])) {
            if ($tenureMonths < (int) $conditions['minTenureMonths']) {
                $reasons[] = sprintf('minimum tenure of %d month(s) not met', (int) $conditions['minTenureMonths']);
            }
        }

        if (isset($conditions[CareerPathConstants::CONDITION_MINIMUM_PERFORMANCE])) {
            $review = $this->performanceReviews->findLatestValidatedForEmployee($employee);
            if (null === $review || null === $review->getScore()) {
                $reasons[] = 'no validated performance review found';
            } elseif ((float) $review->getScore() < (float) $conditions[CareerPathConstants::CONDITION_MINIMUM_PERFORMANCE]) {
                $reasons[] = sprintf(
                    'performance score %.2f is below required minimum %.2f',
                    (float) $review->getScore(),
                    (float) $conditions[CareerPathConstants::CONDITION_MINIMUM_PERFORMANCE]
                );
            }
        }
    }

    /**
     * @param list<string> $reasons
     */
    private function evaluateRequiredSkills(Employee $employee, JobRole $targetJobRole, array &$reasons): void
    {
        $employeeId = $employee->getId();
        if (null === $employeeId) {
            return;
        }

        foreach ($this->requiredSkills->findByJobRole($targetJobRole) as $requirement) {
            $skill = $requirement->getSkill();
            if (null === $skill || null === $skill->getId()) {
                continue;
            }

            $employeeSkill = $this->employeeSkills->findOneByEmployeeAndSkill($employeeId, $skill->getId());
            if (null === $employeeSkill) {
                $reasons[] = sprintf('missing required skill: %s', $skill->getCode() ?? $skill->getId());
                continue;
            }

            if (null === $employeeSkill->getValidatedAt()) {
                $reasons[] = sprintf('skill not validated: %s', $skill->getCode() ?? $skill->getId());
                continue;
            }

            $employeeLevel = $employeeSkill->getLevel();
            $minimumLevel = $requirement->getMinimumLevel();
            if (null === $employeeLevel || null === $minimumLevel) {
                continue;
            }

            if (SkillConstants::getLevelRank($employeeLevel) < SkillConstants::getLevelRank($minimumLevel)) {
                $reasons[] = sprintf(
                    'skill %s level %s is below required %s',
                    $skill->getCode() ?? $skill->getId(),
                    $employeeLevel,
                    $minimumLevel
                );
            }
        }
    }
}
