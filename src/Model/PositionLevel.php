<?php

namespace App\Model;

class PositionLevel
{
    public const string JUNIOR = 'JUNIOR';
    public const string MID_LEVEL = 'MID_LEVEL';
    public const string SENIOR = 'SENIOR';
    public const string MANAGER = 'MANAGER';

    public static function getLevels(): array
    {
        return [
            self::JUNIOR,
            self::MID_LEVEL,
            self::SENIOR,
            self::MANAGER,
        ];
    }
}
