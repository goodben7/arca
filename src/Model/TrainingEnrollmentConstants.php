<?php

namespace App\Model;

class TrainingEnrollmentConstants
{
    public const string STATUS_ASSIGNED = 'ASSIGNED';
    public const string STATUS_IN_PROGRESS = 'IN_PROGRESS';
    public const string STATUS_COMPLETED = 'COMPLETED';
    public const string STATUS_CERTIFIED = 'CERTIFIED';
    public const string STATUS_ABSENT = 'ABSENT';

    /** @deprecated use STATUS_ASSIGNED — conservé pour données legacy avant migration */
    public const string STATUS_ENROLLED = 'ENROLLED';

    public const string ACTION_START = 'START';
    public const string ACTION_COMPLETE = 'COMPLETE';
    public const string ACTION_CERTIFY = 'CERTIFY';
    public const string ACTION_MARK_ABSENT = 'MARK_ABSENT';
    public const string ACTION_SET_ASSIGNED = 'SET_ASSIGNED';

    /** @deprecated use ACTION_SET_ASSIGNED */
    public const string ACTION_SET_ENROLLED = 'SET_ENROLLED';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ASSIGNED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_COMPLETED,
            self::STATUS_CERTIFIED,
            self::STATUS_ABSENT,
        ];
    }

    public static function getActions(): array
    {
        return [
            self::ACTION_START,
            self::ACTION_COMPLETE,
            self::ACTION_CERTIFY,
            self::ACTION_MARK_ABSENT,
            self::ACTION_SET_ASSIGNED,
        ];
    }

    public static function getAllowedActionsForStatus(?string $status): array
    {
        return match ($status) {
            self::STATUS_ASSIGNED, self::STATUS_ENROLLED => [
                self::ACTION_START,
                self::ACTION_MARK_ABSENT,
            ],
            self::STATUS_IN_PROGRESS => [
                self::ACTION_COMPLETE,
                self::ACTION_MARK_ABSENT,
            ],
            self::STATUS_COMPLETED => [self::ACTION_CERTIFY],
            self::STATUS_CERTIFIED => [],
            self::STATUS_ABSENT => [self::ACTION_SET_ASSIGNED],
            default => [],
        };
    }

    public static function normalizeStatus(?string $status): ?string
    {
        return self::STATUS_ENROLLED === $status ? self::STATUS_ASSIGNED : $status;
    }
}
