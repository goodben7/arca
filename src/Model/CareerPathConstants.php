<?php

namespace App\Model;

class CareerPathConstants
{
    public const string CONDITION_MINIMUM_PERFORMANCE = 'minimumPerformance';
    public const string CONDITION_MINIMUM_YEARS = 'minimumYears';
    public const string CONDITION_REQUIRED_SKILLS = 'requiredSkills';
    public const string CONDITION_REQUIRED_TRAININGS = 'requiredTrainings';

    /**
     * @return list<string>
     */
    public static function getKnownConditionKeys(): array
    {
        return [
            self::CONDITION_MINIMUM_PERFORMANCE,
            self::CONDITION_MINIMUM_YEARS,
            self::CONDITION_REQUIRED_SKILLS,
            self::CONDITION_REQUIRED_TRAININGS,
        ];
    }
}
