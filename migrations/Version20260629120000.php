<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260629120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add evaluation_cycle, performance_review and objective tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `evaluation_cycle` (
            EC_ID VARCHAR(16) NOT NULL,
            EC_NAME VARCHAR(120) NOT NULL,
            EC_YEAR SMALLINT NOT NULL,
            EC_STATUS VARCHAR(15) NOT NULL,
            EC_START_DATE DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\',
            EC_END_DATE DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\',
            EC_OPENED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            EC_CLOSED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            EC_CREATED_AT DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            EC_UPDATED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_EC_STATUS (EC_STATUS),
            INDEX IDX_EC_YEAR (EC_YEAR),
            PRIMARY KEY(EC_ID)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE `performance_review` (
            PV_ID VARCHAR(16) NOT NULL,
            PV_EMPLOYEE VARCHAR(16) NOT NULL,
            PV_CYCLE VARCHAR(16) NOT NULL,
            PV_REVIEWER VARCHAR(16) DEFAULT NULL,
            PV_SCORE NUMERIC(4, 2) DEFAULT NULL,
            PV_COMMENT LONGTEXT DEFAULT NULL,
            PV_STATUS VARCHAR(15) NOT NULL,
            PV_SUBMITTED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PV_VALIDATED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PV_VALIDATED_BY VARCHAR(16) DEFAULT NULL,
            PV_CREATED_AT DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PV_UPDATED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_PERFORMANCE_REVIEW_EMPLOYEE_CYCLE (PV_EMPLOYEE, PV_CYCLE),
            INDEX IDX_PV_EMPLOYEE (PV_EMPLOYEE),
            INDEX IDX_PV_CYCLE (PV_CYCLE),
            PRIMARY KEY(PV_ID)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE `objective` (
            OB_ID VARCHAR(16) NOT NULL,
            OB_EMPLOYEE VARCHAR(16) NOT NULL,
            OB_CYCLE VARCHAR(16) NOT NULL,
            OB_TITLE VARCHAR(160) NOT NULL,
            OB_DESCRIPTION LONGTEXT DEFAULT NULL,
            OB_SPECIFIC LONGTEXT NOT NULL,
            OB_MEASURABLE LONGTEXT NOT NULL,
            OB_TARGET_VALUE VARCHAR(120) DEFAULT NULL,
            OB_ACHIEVABLE LONGTEXT DEFAULT NULL,
            OB_RELEVANT LONGTEXT DEFAULT NULL,
            OB_DUE_DATE DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\',
            OB_STATUS VARCHAR(15) NOT NULL,
            OB_CREATED_AT DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            OB_UPDATED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_OB_EMPLOYEE (OB_EMPLOYEE),
            INDEX IDX_OB_CYCLE (OB_CYCLE),
            PRIMARY KEY(OB_ID)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE `performance_review` ADD CONSTRAINT FK_PV_CYCLE FOREIGN KEY (PV_CYCLE) REFERENCES `evaluation_cycle` (EC_ID)');
        $this->addSql('ALTER TABLE `objective` ADD CONSTRAINT FK_OB_CYCLE FOREIGN KEY (OB_CYCLE) REFERENCES `evaluation_cycle` (EC_ID)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `objective` DROP FOREIGN KEY FK_OB_CYCLE');
        $this->addSql('ALTER TABLE `performance_review` DROP FOREIGN KEY FK_PV_CYCLE');
        $this->addSql('DROP TABLE `objective`');
        $this->addSql('DROP TABLE `performance_review`');
        $this->addSql('DROP TABLE `evaluation_cycle`');
    }
}
