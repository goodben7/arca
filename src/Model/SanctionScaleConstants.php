<?php

namespace App\Model;

class SanctionScaleConstants
{
    public const string CODE_REPRIMAND = 'REPRIMAND';
    public const string CODE_WARN = 'WARN';
    public const string CODE_BLAME = 'BLAME';
    public const string CODE_SUSPEND = 'SUSPEND';
    public const string CODE_DISMISS = 'DISMISS';

    public const int SEVERITY_MIN = 1;
    public const int SEVERITY_MAX = 5;

    /**
     * @return list<string>
     */
    public static function getDefaultCodes(): array
    {
        return [
            self::CODE_REPRIMAND,
            self::CODE_WARN,
            self::CODE_BLAME,
            self::CODE_SUSPEND,
            self::CODE_DISMISS,
        ];
    }

    /**
     * Written notices that generate a TYPE_WARNING_LETTER on apply.
     *
     * @return list<string>
     */
    public static function getWrittenNoticeCodes(): array
    {
        return [
            self::CODE_REPRIMAND,
            self::CODE_WARN,
            self::CODE_BLAME,
        ];
    }

    public static function isWrittenNoticeCode(?string $code): bool
    {
        return \in_array($code, self::getWrittenNoticeCodes(), true);
    }
}
