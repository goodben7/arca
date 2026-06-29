<?php

declare(strict_types=1);

/**
 * Fiches métier considérées comme critiques pour la succession.
 * Codes alignés sur app:seed:job-architecture (filière compta).
 */
return static function (): iterable {
    yield 'CFO';
    yield 'ACC-LEAD';
    yield 'ACC-SR';
};
