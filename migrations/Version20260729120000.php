<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260729120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add sanction_scale referential catalog';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `sanction_scale` (
            SS_ID VARCHAR(16) NOT NULL,
            SS_CODE VARCHAR(40) NOT NULL,
            SS_LABEL VARCHAR(120) NOT NULL,
            SS_SEVERITY_LEVEL INT NOT NULL,
            SS_REQUIRES_HEARING TINYINT(1) NOT NULL,
            SS_MAX_DURATION_DAYS INT DEFAULT NULL,
            SS_ACTIVE TINYINT(1) NOT NULL,
            SS_CREATED_AT DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            SS_UPDATED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_SANCTION_SCALE_CODE (SS_CODE),
            INDEX IDX_SS_SEVERITY_LEVEL (SS_SEVERITY_LEVEL),
            INDEX IDX_SS_ACTIVE (SS_ACTIVE),
            PRIMARY KEY(SS_ID)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE `sanction_scale`');
    }
}
