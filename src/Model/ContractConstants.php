<?php

namespace App\Model;

class ContractConstants
{
    public const string STATUS_CDI = 'CDI';
    public const string STATUS_CDD = 'CDD';
    public const string STATUS_INTERNSHIP = 'INTERNSHIP';
    public const string STATUS_CONSULTANT = 'CONSULTANT';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_CDI,
            self::STATUS_CDD,
            self::STATUS_INTERNSHIP,
            self::STATUS_CONSULTANT,
        ];
    }
}
