<?php

namespace App\Model;

class SuccessionPlanConstants
{
    public const string READINESS_READY_NOW = 'READY_NOW';
    public const string READINESS_WITHIN_1_YEAR = 'WITHIN_1_YEAR';
    public const string READINESS_WITHIN_2_YEARS = 'WITHIN_2_YEARS';
    public const string READINESS_DEVELOPMENT_NEEDED = 'DEVELOPMENT_NEEDED';

    public const string STATUS_ACTIVE = 'ACTIVE';
    public const string STATUS_INACTIVE = 'INACTIVE';

    public static function getReadinessLevels(): array
    {
        return [
            self::READINESS_READY_NOW,
            self::READINESS_WITHIN_1_YEAR,
            self::READINESS_WITHIN_2_YEARS,
            self::READINESS_DEVELOPMENT_NEEDED,
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_INACTIVE,
        ];
    }
}
