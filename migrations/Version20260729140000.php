<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260729140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add disciplinary_case table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `disciplinary_case` (
            DS_ID VARCHAR(16) NOT NULL,
            DS_EMPLOYEE VARCHAR(16) NOT NULL,
            DS_SANCTION_SCALE VARCHAR(16) NOT NULL,
            DS_STATUS VARCHAR(30) NOT NULL,
            DS_FACTS LONGTEXT NOT NULL,
            DS_REASON LONGTEXT DEFAULT NULL,
            DS_OCCURRED_AT DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            DS_OPENED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            DS_OPENED_BY VARCHAR(16) DEFAULT NULL,
            DS_HEARING_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            DS_HEARING_BY VARCHAR(16) DEFAULT NULL,
            DS_DECIDED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            DS_DECIDED_BY VARCHAR(16) DEFAULT NULL,
            DS_APPLIED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            DS_APPLIED_BY VARCHAR(16) DEFAULT NULL,
            DS_CLOSED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            DS_CLOSED_BY VARCHAR(16) DEFAULT NULL,
            DS_CANCELLED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            DS_CANCELLED_BY VARCHAR(16) DEFAULT NULL,
            DS_REJECTED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            DS_REJECTED_BY VARCHAR(16) DEFAULT NULL,
            DS_REJECTION_REASON LONGTEXT DEFAULT NULL,
            DS_CREATED_AT DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            DS_UPDATED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_DS_EMPLOYEE (DS_EMPLOYEE),
            INDEX IDX_DS_STATUS (DS_STATUS),
            INDEX IDX_DS_SANCTION_SCALE (DS_SANCTION_SCALE),
            PRIMARY KEY(DS_ID)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `disciplinary_case` ADD CONSTRAINT FK_DS_SANCTION_SCALE FOREIGN KEY (DS_SANCTION_SCALE) REFERENCES `sanction_scale` (SS_ID)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `disciplinary_case` DROP FOREIGN KEY FK_DS_SANCTION_SCALE');
        $this->addSql('DROP TABLE `disciplinary_case`');
    }
}
