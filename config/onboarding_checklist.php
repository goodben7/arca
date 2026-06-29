<?php

declare(strict_types=1);

use App\Model\OnboardingTaskConstants;

/**
 * Checklist onboarding par défaut (configurable).
 *
 * dueDays : échéance relative à startedAt du processus.
 */
return static function (): iterable {
    yield [
        'title' => 'Compléter le dossier administratif',
        'type' => OnboardingTaskConstants::TYPE_HR_FORM,
        'dueDays' => 7,
    ];
    yield [
        'title' => 'Remettre les pièces d\'identité',
        'type' => OnboardingTaskConstants::TYPE_DOCUMENT,
        'dueDays' => 5,
    ];
    yield [
        'title' => 'Créer les accès informatiques',
        'type' => OnboardingTaskConstants::TYPE_IT_ACCESS,
        'dueDays' => 3,
    ];
    yield [
        'title' => 'Attribuer le matériel de travail',
        'type' => OnboardingTaskConstants::TYPE_EQUIPMENT,
        'dueDays' => 5,
    ];
    yield [
        'title' => 'Formation accueil / sécurité',
        'type' => OnboardingTaskConstants::TYPE_TRAINING,
        'dueDays' => 14,
    ];
};
