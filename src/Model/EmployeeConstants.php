<?php

namespace App\Model;

class EmployeeConstants
{
    public const string GENDER_MALE = 'M';
    public const string GENDER_FEMALE = 'F';
    public const string GENDER_OTHER = 'O';

    public const string MARITAL_SINGLE = 'SINGLE';
    public const string MARITAL_MARRIED = 'MARRIED';
    public const string MARITAL_DIVORCED = 'DIVORCED';
    public const string MARITAL_WIDOWED = 'WIDOWED';

    public const string STATUS_ACTIVE = 'ACTIVE';
    public const string STATUS_INACTIVE = 'INACTIVE';
    public const string STATUS_ON_LEAVE = 'ON_LEAVE';

    public static function getGenders(): array
    {
        return [
            self::GENDER_MALE,
            self::GENDER_FEMALE,
            self::GENDER_OTHER,
        ];
    }

    public static function getMaritalStatuses(): array
    {
        return [
            self::MARITAL_SINGLE,
            self::MARITAL_MARRIED,
            self::MARITAL_DIVORCED,
            self::MARITAL_WIDOWED,
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_INACTIVE,
            self::STATUS_ON_LEAVE,
        ];
    }
}
