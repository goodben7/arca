<?php

declare(strict_types=1);

use App\Model\ExitTaskConstants;

/**
 * Checklist offboarding par défaut (configurable).
 *
 * dueDays : échéance relative à startedAt du processus de sortie.
 */
return static function (): iterable {
    yield [
        'title' => 'Transfert de connaissances',
        'type' => ExitTaskConstants::TYPE_KNOWLEDGE_TRANSFER,
        'dueDays' => 7,
    ];
    yield [
        'title' => 'Entretien de sortie',
        'type' => ExitTaskConstants::TYPE_EXIT_INTERVIEW,
        'dueDays' => 5,
    ];
    yield [
        'title' => 'Restitution du matériel',
        'type' => ExitTaskConstants::TYPE_EQUIPMENT_RETURN,
        'dueDays' => 3,
    ];
    yield [
        'title' => 'Révocation des accès informatiques',
        'type' => ExitTaskConstants::TYPE_ACCESS_REVOCATION,
        'dueDays' => 1,
    ];
    yield [
        'title' => 'Formalités RH de départ',
        'type' => ExitTaskConstants::TYPE_HR_FORM,
        'dueDays' => 5,
    ];
};
