<?php

namespace App\Model;

class TrainingEnrollmentConstants
{
    public const string STATUS_ENROLLED = 'ENROLLED';
    public const string STATUS_COMPLETED = 'COMPLETED';
    public const string STATUS_ABSENT = 'ABSENT';

    public const string ACTION_SET_ENROLLED = 'SET_ENROLLED';
    public const string ACTION_COMPLETE = 'COMPLETE';
    public const string ACTION_MARK_ABSENT = 'MARK_ABSENT';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ENROLLED,
            self::STATUS_COMPLETED,
            self::STATUS_ABSENT,
        ];
    }

    public static function getActions(): array
    {
        return [
            self::ACTION_SET_ENROLLED,
            self::ACTION_COMPLETE,
            self::ACTION_MARK_ABSENT,
        ];
    }

    public static function getAllowedActionsForStatus(?string $status): array
    {
        return match ($status) {
            self::STATUS_ENROLLED => [self::ACTION_COMPLETE, self::ACTION_MARK_ABSENT],
            self::STATUS_ABSENT => [self::ACTION_SET_ENROLLED],
            self::STATUS_COMPLETED => [],
            default => [],
        };
    }
}
