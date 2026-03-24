<?php

namespace App\Model;

class ContractConstants
{
    public const string TYPE_CDI = 'CDI';
    public const string TYPE_CDD = 'CDD';
    public const string TYPE_INTERNSHIP = 'INTERNSHIP';
    public const string TYPE_CONSULTANT = 'CONSULTANT';

    public const string STATUS_PENDING = 'PENDING';
    public const string STATUS_ACTIVE = 'ACTIVE';
    public const string STATUS_ENDED = 'ENDED';
    public const string STATUS_CANCELLED = 'CANCELLED';

    public const string ACTION_ACTIVATE = 'ACTIVATE';
    public const string ACTION_END = 'END';
    public const string ACTION_CANCEL = 'CANCEL';
    public const string ACTION_SET_PENDING = 'SET_PENDING';

    public const string STATUS_CDI = self::TYPE_CDI;
    public const string STATUS_CDD = self::TYPE_CDD;
    public const string STATUS_INTERNSHIP = self::TYPE_INTERNSHIP;
    public const string STATUS_CONSULTANT = self::TYPE_CONSULTANT;

    public static function getTypes(): array
    {
        return [
            self::TYPE_CDI,
            self::TYPE_CDD,
            self::TYPE_INTERNSHIP,
            self::TYPE_CONSULTANT,
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_ACTIVE,
            self::STATUS_ENDED,
            self::STATUS_CANCELLED,
        ];
    }

    public static function getActions(): array
    {
        return [
            self::ACTION_ACTIVATE,
            self::ACTION_END,
            self::ACTION_CANCEL,
            self::ACTION_SET_PENDING,
        ];
    }

    public static function getAllowedActionsForStatus(?string $status): array
    {
        return match ($status) {
            self::STATUS_PENDING => [self::ACTION_ACTIVATE, self::ACTION_CANCEL],
            self::STATUS_ACTIVE => [self::ACTION_END, self::ACTION_CANCEL, self::ACTION_SET_PENDING],
            self::STATUS_ENDED, self::STATUS_CANCELLED => [],
            default => [],
        };
    }
}
