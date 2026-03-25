<?php

namespace App\Model;

class JobOfferConstants
{
    public const string STATUS_DRAFT = 'DRAFT';
    public const string STATUS_PUBLISHED = 'PUBLISHED';
    public const string STATUS_CLOSED = 'CLOSED';

    public const string ACTION_SET_DRAFT = 'SET_DRAFT';
    public const string ACTION_PUBLISH = 'PUBLISH';
    public const string ACTION_CLOSE = 'CLOSE';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_PUBLISHED,
            self::STATUS_CLOSED,
        ];
    }

    public static function getActions(): array
    {
        return [
            self::ACTION_SET_DRAFT,
            self::ACTION_PUBLISH,
            self::ACTION_CLOSE,
        ];
    }

    public static function getAllowedActionsForStatus(?string $status): array
    {
        return match ($status) {
            self::STATUS_DRAFT => [self::ACTION_PUBLISH],
            self::STATUS_PUBLISHED => [self::ACTION_CLOSE, self::ACTION_SET_DRAFT],
            self::STATUS_CLOSED => [],
            default => [],
        };
    }
}
