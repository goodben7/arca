<?php

namespace App\Model;

class TrainingSessionConstants
{
    public const string STATUS_PLANNED = 'PLANNED';
    public const string STATUS_ONGOING = 'ONGOING';
    public const string STATUS_COMPLETED = 'COMPLETED';
    public const string STATUS_CANCELLED = 'CANCELLED';

    public const string ACTION_SET_PLANNED = 'SET_PLANNED';
    public const string ACTION_START = 'START';
    public const string ACTION_COMPLETE = 'COMPLETE';
    public const string ACTION_CANCEL = 'CANCEL';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PLANNED,
            self::STATUS_ONGOING,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }

    public static function getActions(): array
    {
        return [
            self::ACTION_SET_PLANNED,
            self::ACTION_START,
            self::ACTION_COMPLETE,
            self::ACTION_CANCEL,
        ];
    }

    public static function getAllowedActionsForStatus(?string $status): array
    {
        return match ($status) {
            self::STATUS_PLANNED => [self::ACTION_START, self::ACTION_CANCEL],
            self::STATUS_ONGOING => [self::ACTION_COMPLETE, self::ACTION_CANCEL, self::ACTION_SET_PLANNED],
            self::STATUS_COMPLETED, self::STATUS_CANCELLED => [],
            default => [],
        };
    }
}
