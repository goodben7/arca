<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260818120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add explanation request and appeal deadline fields to disciplinary_case';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `disciplinary_case`
            ADD DS_EXPLANATION_REQUESTED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            ADD DS_EXPLANATION_DUE_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            ADD DS_EXPLANATION_TEXT LONGTEXT DEFAULT NULL,
            ADD DS_APPEAL_DEADLINE_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `disciplinary_case`
            DROP DS_EXPLANATION_REQUESTED_AT,
            DROP DS_EXPLANATION_DUE_AT,
            DROP DS_EXPLANATION_TEXT,
            DROP DS_APPEAL_DEADLINE_AT
        ');
    }
}
