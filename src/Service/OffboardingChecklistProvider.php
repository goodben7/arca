<?php

namespace App\Service;

class OffboardingChecklistProvider
{
    /** @var list<array{title: string, type: string, dueDays: int}> */
    private array $checklistItems;

    public function __construct()
    {
        $loader = require \dirname(__DIR__, 2).'/config/offboarding_checklist.php';
        $this->checklistItems = is_callable($loader) ? iterator_to_array($loader()) : [];
    }

    /**
     * @return list<array{title: string, type: string, dueDays: int}>
     */
    public function getDefaultItems(): array
    {
        return $this->checklistItems;
    }
}
