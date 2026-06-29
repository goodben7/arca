<?php

namespace App\Model;

class JourneyStageConstants
{
    public const string CANDIDATE = 'CANDIDATE';
    public const string ONBOARDING = 'ONBOARDING';
    public const string ACTIVE = 'ACTIVE';
    public const string PROMOTION = 'PROMOTION';
    public const string TRANSFER = 'TRANSFER';
    public const string TRAINING = 'TRAINING';
    public const string LEAVE = 'LEAVE';
    public const string OFFBOARDING = 'OFFBOARDING';
    public const string RETIREMENT = 'RETIREMENT';
    public const string ARCHIVED = 'ARCHIVED';

    public static function getStages(): array
    {
        return [
            self::CANDIDATE,
            self::ONBOARDING,
            self::ACTIVE,
            self::PROMOTION,
            self::TRANSFER,
            self::TRAINING,
            self::LEAVE,
            self::OFFBOARDING,
            self::RETIREMENT,
            self::ARCHIVED,
        ];
    }
}
