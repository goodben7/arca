<?php

namespace App\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Model\HrDashboardSummary;
use App\Service\HrDashboard\HrDashboardCalculator;

class HrDashboardProvider implements ProviderInterface
{
    public function __construct(private HrDashboardCalculator $calculator)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): HrDashboardSummary
    {
        $data = $this->calculator->compute();

        return new HrDashboardSummary(
            $data['headcount'],
            $data['departuresLast12Months'],
            $data['turnoverRatePercent'],
            $data['promotionsLast12Months'],
            $data['trainingsInProgress'],
            $data['trainingsCertifiedLast12Months'],
            $data['criticalJobRolesTotal'],
            $data['criticalJobRolesCovered'],
            $data['successionCoveragePercent'],
            $data['criticalSkillGaps'],
            $data['periodMonths'],
            $data['computedAt'],
        );
    }
}
