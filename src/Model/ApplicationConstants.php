<?php

namespace App\Model;

class ApplicationConstants
{
    public const string STATUS_APPLIED = 'APPLIED';
    public const string STATUS_SHORTLISTED = 'SHORTLISTED';
    public const string STATUS_INTERVIEW = 'INTERVIEW';
    public const string STATUS_REJECTED = 'REJECTED';
    public const string STATUS_HIRED = 'HIRED';

    public const string ACTION_SET_APPLIED = 'SET_APPLIED';
    public const string ACTION_SET_SHORTLISTED = 'SET_SHORTLISTED';
    public const string ACTION_SET_INTERVIEW = 'SET_INTERVIEW';
    public const string ACTION_REJECT = 'REJECT';
    public const string ACTION_HIRE = 'HIRE';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_APPLIED,
            self::STATUS_SHORTLISTED,
            self::STATUS_INTERVIEW,
            self::STATUS_REJECTED,
            self::STATUS_HIRED,
        ];
    }

    public static function getActions(): array
    {
        return [
            self::ACTION_SET_APPLIED,
            self::ACTION_SET_SHORTLISTED,
            self::ACTION_SET_INTERVIEW,
            self::ACTION_REJECT,
            self::ACTION_HIRE,
        ];
    }

    public static function getAllowedActionsForStatus(?string $status): array
    {
        return match ($status) {
            self::STATUS_APPLIED => [self::ACTION_SET_SHORTLISTED, self::ACTION_REJECT],
            self::STATUS_SHORTLISTED => [self::ACTION_SET_INTERVIEW, self::ACTION_REJECT, self::ACTION_SET_APPLIED],
            self::STATUS_INTERVIEW => [self::ACTION_HIRE, self::ACTION_REJECT, self::ACTION_SET_SHORTLISTED],
            self::STATUS_REJECTED, self::STATUS_HIRED => [],
            default => [],
        };
    }
}
