<?php

namespace App\Model;

class TrainingRequestConstants
{
    public const string PRIORITY_LOW = 'LOW';
    public const string PRIORITY_MEDIUM = 'MEDIUM';
    public const string PRIORITY_HIGH = 'HIGH';

    public const string STATUS_PENDING = 'PENDING';
    public const string STATUS_APPROVED = 'APPROVED';
    public const string STATUS_REJECTED = 'REJECTED';

    public static function getPriorities(): array
    {
        return [
            self::PRIORITY_LOW,
            self::PRIORITY_MEDIUM,
            self::PRIORITY_HIGH,
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
        ];
    }
}
