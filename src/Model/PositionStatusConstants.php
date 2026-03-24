<?php

namespace App\Model;

class PositionStatusConstants
{
    public const string STATUS_OPEN = 'OPEN';
    public const string STATUS_CLOSED = 'CLOSED';

    public const string ACTION_OPEN = 'OPEN';
    public const string ACTION_CLOSE = 'CLOSE';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_OPEN,
            self::STATUS_CLOSED,
        ];
    }

    public static function getActions(): array
    {
        return [
            self::ACTION_OPEN,
            self::ACTION_CLOSE,
        ];
    }

    public static function getAllowedActionsForStatus(?string $status): array
    {
        return match ($status) {
            self::STATUS_OPEN => [self::ACTION_CLOSE],
            self::STATUS_CLOSED => [self::ACTION_OPEN],
            default => [],
        };
    }
}
