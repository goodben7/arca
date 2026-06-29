<?php

namespace App\Model;

class CompensationHistoryConstants
{
    public const string SOURCE_MOBILITY_IMPLEMENTED = 'MOBILITY_IMPLEMENTED';
    public const string SOURCE_MANUAL = 'MANUAL';

    public static function getSourceEvents(): array
    {
        return [
            self::SOURCE_MOBILITY_IMPLEMENTED,
            self::SOURCE_MANUAL,
        ];
    }
}
