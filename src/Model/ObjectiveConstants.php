<?php

namespace App\Model;

class ObjectiveConstants
{
    public const string STATUS_DRAFT = 'DRAFT';
    public const string STATUS_ACTIVE = 'ACTIVE';
    public const string STATUS_COMPLETED = 'COMPLETED';
    public const string STATUS_CANCELLED = 'CANCELLED';

    public const string ACTION_ACTIVATE = 'ACTIVATE';
    public const string ACTION_COMPLETE = 'COMPLETE';
    public const string ACTION_CANCEL = 'CANCEL';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_ACTIVE,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }

    public static function getAllowedActionsForStatus(?string $status): array
    {
        return match ($status) {
            self::STATUS_DRAFT => [self::ACTION_ACTIVATE, self::ACTION_CANCEL],
            self::STATUS_ACTIVE => [self::ACTION_COMPLETE, self::ACTION_CANCEL],
            self::STATUS_COMPLETED, self::STATUS_CANCELLED => [],
            default => [],
        };
    }
}
