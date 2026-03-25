<?php

namespace App\Model;

class RecruitmentRequestConstants
{
    public const string STATUS_PENDING = 'PENDING';
    public const string STATUS_APPROVED = 'APPROVED';
    public const string STATUS_REJECTED = 'REJECTED';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
        ];
    }
}

