<?php

namespace App\Model;

class PerformanceReviewConstants
{
    public const string STATUS_DRAFT = 'DRAFT';
    public const string STATUS_SUBMITTED = 'SUBMITTED';
    public const string STATUS_VALIDATED = 'VALIDATED';

    public const string ACTION_SUBMIT = 'SUBMIT';
    public const string ACTION_VALIDATE = 'VALIDATE';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_SUBMITTED,
            self::STATUS_VALIDATED,
        ];
    }

    public static function getAllowedActionsForStatus(?string $status): array
    {
        return match ($status) {
            self::STATUS_DRAFT => [self::ACTION_SUBMIT],
            self::STATUS_SUBMITTED => [self::ACTION_VALIDATE],
            self::STATUS_VALIDATED => [],
            default => [],
        };
    }
}
