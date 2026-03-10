<?php

namespace App\Model;

class PositionStatusConstants
{
    public const string STATUS_OPEN = 'OPEN';
    public const string STATUS_CLOSED = 'CLOSED';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_OPEN,
            self::STATUS_CLOSED,
        ];
    }
}
