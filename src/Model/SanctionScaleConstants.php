<?php

namespace App\Model;

class SanctionScaleConstants
{
    public const string CODE_WARN = 'WARN';
    public const string CODE_BLAME = 'BLAME';
    public const string CODE_SUSPEND = 'SUSPEND';
    public const string CODE_DISMISS = 'DISMISS';

    public const int SEVERITY_MIN = 1;
    public const int SEVERITY_MAX = 4;

    /**
     * @return list<string>
     */
    public static function getDefaultCodes(): array
    {
        return [
            self::CODE_WARN,
            self::CODE_BLAME,
            self::CODE_SUSPEND,
            self::CODE_DISMISS,
        ];
    }
}
