<?php

namespace App\Model;

class CareerPlanConstants
{
    public const string STATUS_ACTIVE = 'ACTIVE';
    public const string STATUS_ACHIEVED = 'ACHIEVED';
    public const string STATUS_CANCELLED = 'CANCELLED';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_ACHIEVED,
            self::STATUS_CANCELLED,
        ];
    }
}
