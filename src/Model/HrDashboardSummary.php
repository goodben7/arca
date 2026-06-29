<?php

namespace App\Model;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Provider\HrDashboardProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    normalizationContext: ['groups' => 'hr_dashboard:get'],
    operations: [
        new Get(
            uriTemplate: '/hr/dashboard',
            security: 'is_granted("ROLE_HR_DASHBOARD_VIEW")',
            provider: HrDashboardProvider::class,
        ),
    ],
)]
class HrDashboardSummary
{
    public function __construct(
        #[Groups(['hr_dashboard:get'])]
        public readonly int $headcount,
        #[Groups(['hr_dashboard:get'])]
        public readonly int $departuresLast12Months,
        #[Groups(['hr_dashboard:get'])]
        public readonly float $turnoverRatePercent,
        #[Groups(['hr_dashboard:get'])]
        public readonly int $promotionsLast12Months,
        #[Groups(['hr_dashboard:get'])]
        public readonly int $trainingsInProgress,
        #[Groups(['hr_dashboard:get'])]
        public readonly int $trainingsCertifiedLast12Months,
        #[Groups(['hr_dashboard:get'])]
        public readonly int $criticalJobRolesTotal,
        #[Groups(['hr_dashboard:get'])]
        public readonly int $criticalJobRolesCovered,
        #[Groups(['hr_dashboard:get'])]
        public readonly float $successionCoveragePercent,
        #[Groups(['hr_dashboard:get'])]
        public readonly int $criticalSkillGaps,
        #[Groups(['hr_dashboard:get'])]
        public readonly int $periodMonths,
        #[Groups(['hr_dashboard:get'])]
        public readonly \DateTimeImmutable $computedAt,
    ) {
    }
}
