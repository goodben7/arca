<?php

namespace App\Policy;

final class PolicyResult
{
    /**
     * @param list<string> $reasons
     */
    private function __construct(
        private readonly bool $eligible,
        private readonly array $reasons,
    ) {
    }

    public static function eligible(): self
    {
        return new self(true, []);
    }

    /**
     * @param list<string> $reasons
     */
    public static function notEligible(array $reasons): self
    {
        return new self(false, array_values($reasons));
    }

    public function isEligible(): bool
    {
        return $this->eligible;
    }

    /**
     * @return list<string>
     */
    public function getReasons(): array
    {
        return $this->reasons;
    }
}
