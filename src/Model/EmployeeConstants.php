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
    public const string STATUS_SUSPENDED = 'SUSPENDED';
    public const string STATUS_TERMINATED = 'TERMINATED';
    public const string STATUS_PROBATION = 'PROBATION';
    public const string STATUS_RETIRED = 'RETIRED';

    public const string ACTION_ACTIVATE = 'ACTIVATE';
    public const string ACTION_DEACTIVATE = 'DEACTIVATE';
    public const string ACTION_SET_ON_LEAVE = 'SET_ON_LEAVE';
    public const string ACTION_SUSPEND = 'SUSPEND';
    public const string ACTION_TERMINATE = 'TERMINATE';
    public const string ACTION_RETIRE = 'RETIRE';
    public const string ACTION_SET_PROBATION = 'SET_PROBATION';
    public const string ACTION_ASSIGN_MANAGER = 'ASSIGN_MANAGER';

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
            self::STATUS_SUSPENDED,
            self::STATUS_TERMINATED,
            self::STATUS_PROBATION,
            self::STATUS_RETIRED,
        ];
    }

    public static function getActions(): array
    {
        return [
            self::ACTION_ACTIVATE,
            self::ACTION_DEACTIVATE,
            self::ACTION_SET_ON_LEAVE,
            self::ACTION_SUSPEND,
            self::ACTION_TERMINATE,
            self::ACTION_RETIRE,
            self::ACTION_SET_PROBATION,
            self::ACTION_ASSIGN_MANAGER,
        ];
    }

    public static function getAllowedActionsForStatus(?string $status): array
    {
        return match ($status) {
            self::STATUS_INACTIVE => [self::ACTION_ACTIVATE, self::ACTION_SET_PROBATION, self::ACTION_TERMINATE, self::ACTION_RETIRE, self::ACTION_ASSIGN_MANAGER],
            self::STATUS_PROBATION => [self::ACTION_ACTIVATE, self::ACTION_DEACTIVATE, self::ACTION_SUSPEND, self::ACTION_TERMINATE, self::ACTION_ASSIGN_MANAGER],
            self::STATUS_ACTIVE => [self::ACTION_DEACTIVATE, self::ACTION_SET_ON_LEAVE, self::ACTION_SUSPEND, self::ACTION_TERMINATE, self::ACTION_RETIRE, self::ACTION_ASSIGN_MANAGER],
            self::STATUS_ON_LEAVE => [self::ACTION_ACTIVATE, self::ACTION_DEACTIVATE, self::ACTION_SUSPEND, self::ACTION_TERMINATE, self::ACTION_RETIRE, self::ACTION_ASSIGN_MANAGER],
            self::STATUS_SUSPENDED => [self::ACTION_ACTIVATE, self::ACTION_DEACTIVATE, self::ACTION_TERMINATE, self::ACTION_RETIRE, self::ACTION_ASSIGN_MANAGER],
            self::STATUS_TERMINATED, self::STATUS_RETIRED => [],
            default => [],
        };
    }
}
