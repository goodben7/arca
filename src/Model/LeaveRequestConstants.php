<?php

namespace App\Model;

class LeaveRequestConstants
{
    public const string TYPE_ANNUAL = 'ANNUAL';
    public const string TYPE_SICK = 'SICK';
    public const string TYPE_MATERNITY = 'MAT';
    public const string TYPE_PATERNITY = 'PAT';
    public const string TYPE_UNPAID = 'UNPAID';
    public const string TYPE_OTHER = 'OTHER';

    public const string STATUS_PENDING = 'PENDING';
    public const string STATUS_APPROVED = 'APPROVED';
    public const string STATUS_REJECTED = 'REJECTED';
    public const string STATUS_CANCELLED = 'CANCELLED';

    public static function getTypes(): array
    {
        return [
            self::TYPE_ANNUAL,
            self::TYPE_SICK,
            self::TYPE_MATERNITY,
            self::TYPE_PATERNITY,
            self::TYPE_UNPAID,
            self::TYPE_OTHER,
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_CANCELLED,
        ];
    }
}
