<?php

namespace App\Model;

class ExitTaskConstants
{
    public const string TYPE_KNOWLEDGE_TRANSFER = 'KNOWLEDGE_TRANSFER';
    public const string TYPE_EXIT_INTERVIEW = 'EXIT_INTERVIEW';
    public const string TYPE_EQUIPMENT_RETURN = 'EQUIPMENT_RETURN';
    public const string TYPE_ACCESS_REVOCATION = 'ACCESS_REVOCATION';
    public const string TYPE_HR_FORM = 'HR_FORM';

    public const string STATUS_PENDING = 'PENDING';
    public const string STATUS_IN_PROGRESS = 'IN_PROGRESS';
    public const string STATUS_COMPLETED = 'COMPLETED';
    public const string STATUS_CANCELLED = 'CANCELLED';

    public const string ACTION_START = 'START';
    public const string ACTION_COMPLETE = 'COMPLETE';
    public const string ACTION_CANCEL = 'CANCEL';

    public static function getTypes(): array
    {
        return [
            self::TYPE_KNOWLEDGE_TRANSFER,
            self::TYPE_EXIT_INTERVIEW,
            self::TYPE_EQUIPMENT_RETURN,
            self::TYPE_ACCESS_REVOCATION,
            self::TYPE_HR_FORM,
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_IN_PROGRESS,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }

    public static function getAllowedActionsForStatus(?string $status): array
    {
        return match ($status) {
            self::STATUS_PENDING => [self::ACTION_START, self::ACTION_CANCEL],
            self::STATUS_IN_PROGRESS => [self::ACTION_COMPLETE, self::ACTION_CANCEL],
            self::STATUS_COMPLETED, self::STATUS_CANCELLED => [],
            default => [],
        };
    }
}
