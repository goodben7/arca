<?php

namespace App\Model;

class EligibilityActionConstants
{
    public const string PROMOTION = 'PROMOTION';
    public const string RETIREMENT = 'RETIREMENT';

    public static function getActions(): array
    {
        return [
            self::PROMOTION,
            self::RETIREMENT,
        ];
    }
}
