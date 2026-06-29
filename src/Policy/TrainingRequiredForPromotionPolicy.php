<?php

namespace App\Policy;

use App\Entity\CareerPath;
use App\Entity\Employee;
use App\Entity\JobRole;
use App\Entity\TrainingCatalog;
use App\Manager\CareerPathManager;
use App\Manager\JobRoleManager;
use App\Model\CareerPathConstants;
use App\Model\EligibilityActionConstants;
use App\Repository\JobRoleRequiredTrainingRepository;
use App\Repository\TrainingEnrollmentRepository;

class TrainingRequiredForPromotionPolicy implements EligibilityPolicyInterface
{
    public function __construct(
        private JobRoleManager $jobRoles,
        private CareerPathManager $careerPaths,
        private JobRoleRequiredTrainingRepository $requiredTrainings,
        private TrainingEnrollmentRepository $enrollments,
    ) {
    }

    public function supports(string $action): bool
    {
        return EligibilityActionConstants::PROMOTION === $action;
    }

    public function evaluate(Employee $employee, array $context = []): PolicyResult
    {
        $targetJobRoleId = $context['targetJobRoleId'] ?? null;
        if (!$targetJobRoleId) {
            return PolicyResult::eligible();
        }

        $employeeId = $employee->getId();
        if (null === $employeeId) {
            return PolicyResult::notEligible(['employee id is required']);
        }

        $targetJobRole = $this->jobRoles->find($targetJobRoleId);
        $reasons = [];

        $this->evaluateJobRoleRequirements($employeeId, $targetJobRole, $reasons);

        $currentJobRole = $employee->getJobRole();
        if (null !== $currentJobRole) {
            $careerPath = $this->careerPaths->findByTransition($currentJobRole, $targetJobRole);
            if (null !== $careerPath) {
                $this->evaluateCareerPathTrainings($employeeId, $careerPath, $reasons);
            }
        }

        return [] === $reasons
            ? PolicyResult::eligible()
            : PolicyResult::notEligible($reasons);
    }

    /**
     * @param list<string> $reasons
     */
    private function evaluateJobRoleRequirements(string $employeeId, JobRole $targetJobRole, array &$reasons): void
    {
        foreach ($this->requiredTrainings->findByJobRole($targetJobRole) as $requirement) {
            $catalog = $requirement->getCatalogItem();
            if (!$catalog instanceof TrainingCatalog || null === $catalog->getId()) {
                continue;
            }

            if (!$this->enrollments->hasCertifiedCatalogForEmployee($employeeId, $catalog->getId())) {
                $reasons[] = sprintf(
                    'missing certified training: %s',
                    $catalog->getTitle() ?? $catalog->getId()
                );
            }
        }
    }

    /**
     * @param list<string> $reasons
     */
    private function evaluateCareerPathTrainings(string $employeeId, CareerPath $careerPath, array &$reasons): void
    {
        $conditions = $careerPath->getConditions() ?? [];
        $requiredCatalogIds = $conditions[CareerPathConstants::CONDITION_REQUIRED_TRAININGS] ?? [];

        if (!\is_array($requiredCatalogIds)) {
            return;
        }

        foreach ($requiredCatalogIds as $catalogId) {
            if (!\is_string($catalogId) || '' === $catalogId) {
                continue;
            }

            if (!$this->enrollments->hasCertifiedCatalogForEmployee($employeeId, $catalogId)) {
                $reasons[] = sprintf('missing certified training catalog item: %s', $catalogId);
            }
        }
    }
}
