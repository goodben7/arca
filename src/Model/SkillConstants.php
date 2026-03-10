<?php

namespace App\Model;

class SkillConstants
{
    public const string LEVEL_BEGINNER = 'BEGINNER';
    public const string LEVEL_INTERMEDIATE = 'INTERMEDIATE';
    public const string LEVEL_ADVANCED = 'ADVANCED';
    public const string LEVEL_EXPERT = 'EXPERT';

    public static function getLevels(): array
    {
        return [
            self::LEVEL_BEGINNER,
            self::LEVEL_INTERMEDIATE,
            self::LEVEL_ADVANCED,
            self::LEVEL_EXPERT,
        ];
    }
}
