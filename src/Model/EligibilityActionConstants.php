<?php

namespace App\Model;

class EligibilityActionConstants
{
    public const string PROMOTION = 'PROMOTION';

    public static function getActions(): array
    {
        return [
            self::PROMOTION,
        ];
    }
}
