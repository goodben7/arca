<?php

namespace App\Model;

class JourneyEventTypeConstants
{
    public const string HIRED = 'HIRED';
    public const string CREATED = 'CREATED';
    public const string ACTIVATED = 'ACTIVATED';
    public const string DEACTIVATED = 'DEACTIVATED';
    public const string ON_LEAVE = 'ON_LEAVE';
    public const string SUSPENDED = 'SUSPENDED';
    public const string TERMINATED = 'TERMINATED';
    public const string RETIRED = 'RETIRED';
    public const string PROBATION = 'PROBATION';
    public const string MANAGER_ASSIGNED = 'MANAGER_ASSIGNED';
    public const string JOB_ROLE_ASSIGNED = 'JOB_ROLE_ASSIGNED';
    public const string SKILL_VALIDATED = 'SKILL_VALIDATED';
    public const string SKILL_LEVEL_UPGRADED = 'SKILL_LEVEL_UPGRADED';
    public const string PROMOTED = 'PROMOTED';
    public const string TRANSFERRED = 'TRANSFERRED';
    public const string TRAINING_STARTED = 'TRAINING_STARTED';
    public const string TRAINING_COMPLETED = 'TRAINING_COMPLETED';
    public const string TRAINING_CERTIFIED = 'TRAINING_CERTIFIED';
    public const string CONTRACT_ACTIVATED = 'CONTRACT_ACTIVATED';
    public const string CONTRACT_ENDED = 'CONTRACT_ENDED';
    public const string LEAVE_APPROVED = 'LEAVE_APPROVED';
    public const string ONBOARDING_STARTED = 'ONBOARDING_STARTED';
    public const string ONBOARDING_COMPLETED = 'ONBOARDING_COMPLETED';
    public const string OFFBOARDING_STARTED = 'OFFBOARDING_STARTED';
    public const string ARCHIVED = 'ARCHIVED';

    public static function getEventTypes(): array
    {
        return [
            self::HIRED,
            self::CREATED,
            self::ACTIVATED,
            self::DEACTIVATED,
            self::ON_LEAVE,
            self::SUSPENDED,
            self::TERMINATED,
            self::RETIRED,
            self::PROBATION,
            self::MANAGER_ASSIGNED,
            self::JOB_ROLE_ASSIGNED,
            self::SKILL_VALIDATED,
            self::SKILL_LEVEL_UPGRADED,
            self::PROMOTED,
            self::TRANSFERRED,
            self::TRAINING_STARTED,
            self::TRAINING_COMPLETED,
            self::TRAINING_CERTIFIED,
            self::CONTRACT_ACTIVATED,
            self::CONTRACT_ENDED,
            self::LEAVE_APPROVED,
            self::ONBOARDING_STARTED,
            self::ONBOARDING_COMPLETED,
            self::OFFBOARDING_STARTED,
            self::ARCHIVED,
        ];
    }
}
