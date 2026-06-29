<?php

namespace App\Model;

class BenefitConstants
{
    public const string TYPE_HEALTH = 'HEALTH';
    public const string TYPE_TRANSPORT = 'TRANSPORT';
    public const string TYPE_MEAL = 'MEAL';
    public const string TYPE_OTHER = 'OTHER';

    public static function getTypes(): array
    {
        return [
            self::TYPE_HEALTH,
            self::TYPE_TRANSPORT,
            self::TYPE_MEAL,
            self::TYPE_OTHER,
        ];
    }
}
