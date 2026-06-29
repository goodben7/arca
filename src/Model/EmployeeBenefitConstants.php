<?php

namespace App\Model;

class EmployeeBenefitConstants
{
    public const string STATUS_ACTIVE = 'ACTIVE';
    public const string STATUS_ENDED = 'ENDED';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_ENDED,
        ];
    }
}
