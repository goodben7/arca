<?php

namespace App\Model;

class MobilityRequestConstants
{
    public const string TYPE_TRANSFER = 'TRANSFER';
    public const string TYPE_PROMOTION = 'PROMOTION';
    public const string TYPE_DEMOTION = 'DEMOTION';
    public const string TYPE_SECONDMENT = 'SECONDMENT';

    public const string STATUS_DRAFT = 'DRAFT';
    public const string STATUS_MANAGER_APPROVAL = 'MANAGER_APPROVAL';
    public const string STATUS_HR_APPROVAL = 'HR_APPROVAL';
    public const string STATUS_EXECUTIVE_APPROVAL = 'EXECUTIVE_APPROVAL';
    public const string STATUS_IMPLEMENTED = 'IMPLEMENTED';
    public const string STATUS_REJECTED = 'REJECTED';
    public const string STATUS_CANCELLED = 'CANCELLED';

    public const string ACTION_SUBMIT = 'SUBMIT';
    public const string ACTION_APPROVE = 'APPROVE';
    public const string ACTION_REJECT = 'REJECT';
    public const string ACTION_CANCEL = 'CANCEL';

    public static function getTypes(): array
    {
        return [
            self::TYPE_TRANSFER,
            self::TYPE_PROMOTION,
            self::TYPE_DEMOTION,
            self::TYPE_SECONDMENT,
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_MANAGER_APPROVAL,
            self::STATUS_HR_APPROVAL,
            self::STATUS_EXECUTIVE_APPROVAL,
            self::STATUS_IMPLEMENTED,
            self::STATUS_REJECTED,
            self::STATUS_CANCELLED,
        ];
    }

    public static function getAllowedActionsForStatus(?string $status): array
    {
        return match ($status) {
            self::STATUS_DRAFT => [self::ACTION_SUBMIT, self::ACTION_CANCEL],
            self::STATUS_MANAGER_APPROVAL,
            self::STATUS_HR_APPROVAL,
            self::STATUS_EXECUTIVE_APPROVAL => [self::ACTION_APPROVE, self::ACTION_REJECT],
            self::STATUS_IMPLEMENTED,
            self::STATUS_REJECTED,
            self::STATUS_CANCELLED => [],
            default => [],
        };
    }
}
