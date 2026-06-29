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

    public static function getLevelRank(string $level): int
    {
        return match ($level) {
            self::LEVEL_BEGINNER => 1,
            self::LEVEL_INTERMEDIATE => 2,
            self::LEVEL_ADVANCED => 3,
            self::LEVEL_EXPERT => 4,
            default => throw new \InvalidArgumentException(sprintf('invalid skill level: %s', $level)),
        };
    }

    public static function isLevelUpgrade(string $fromLevel, string $toLevel): bool
    {
        return self::getLevelRank($toLevel) > self::getLevelRank($fromLevel);
    }
}
