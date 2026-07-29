<?php

namespace App\Model;

class DisciplinaryCaseConstants
{
    public const string STATUS_DRAFT = 'DRAFT';
    public const string STATUS_OPENED = 'OPENED';
    public const string STATUS_HEARING_SCHEDULED = 'HEARING_SCHEDULED';
    public const string STATUS_DECISION_PENDING = 'DECISION_PENDING';
    public const string STATUS_SANCTION_APPLIED = 'SANCTION_APPLIED';
    public const string STATUS_CLOSED = 'CLOSED';
    public const string STATUS_CANCELLED = 'CANCELLED';
    public const string STATUS_REJECTED = 'REJECTED';

    public const string ACTION_OPEN = 'OPEN';
    public const string ACTION_SCHEDULE_HEARING = 'SCHEDULE_HEARING';
    public const string ACTION_DECIDE = 'DECIDE';
    public const string ACTION_APPLY = 'APPLY';
    public const string ACTION_CANCEL = 'CANCEL';
    public const string ACTION_REJECT = 'REJECT';
    public const string ACTION_CLOSE = 'CLOSE';

    /**
     * @return list<string>
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_OPENED,
            self::STATUS_HEARING_SCHEDULED,
            self::STATUS_DECISION_PENDING,
            self::STATUS_SANCTION_APPLIED,
            self::STATUS_CLOSED,
            self::STATUS_CANCELLED,
            self::STATUS_REJECTED,
        ];
    }

    /**
     * @return list<string>
     */
    public static function getActions(): array
    {
        return [
            self::ACTION_OPEN,
            self::ACTION_SCHEDULE_HEARING,
            self::ACTION_DECIDE,
            self::ACTION_APPLY,
            self::ACTION_CANCEL,
            self::ACTION_REJECT,
            self::ACTION_CLOSE,
        ];
    }

    /**
     * @return list<string>
     */
    public static function getTerminalStatuses(): array
    {
        return [
            self::STATUS_CLOSED,
            self::STATUS_CANCELLED,
            self::STATUS_REJECTED,
        ];
    }

    /**
     * @return list<string>
     */
    public static function getAllowedActionsForStatus(?string $status, ?bool $requiresHearing = null): array
    {
        return match ($status) {
            self::STATUS_DRAFT => [self::ACTION_OPEN, self::ACTION_CANCEL],
            self::STATUS_OPENED => false === $requiresHearing
                ? [self::ACTION_DECIDE, self::ACTION_CANCEL, self::ACTION_REJECT]
                : [self::ACTION_SCHEDULE_HEARING, self::ACTION_CANCEL, self::ACTION_REJECT],
            self::STATUS_HEARING_SCHEDULED => [self::ACTION_DECIDE, self::ACTION_CANCEL, self::ACTION_REJECT],
            self::STATUS_DECISION_PENDING => [self::ACTION_APPLY, self::ACTION_CANCEL, self::ACTION_REJECT],
            self::STATUS_SANCTION_APPLIED => [self::ACTION_CLOSE],
            self::STATUS_CLOSED, self::STATUS_CANCELLED, self::STATUS_REJECTED => [],
            default => [],
        };
    }
}
