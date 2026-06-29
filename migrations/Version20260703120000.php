<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260703120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add benefits, employee benefits, exit process and exit tasks';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `benefit` (
            BF_ID VARCHAR(16) NOT NULL,
            BF_CODE VARCHAR(40) NOT NULL,
            BF_NAME VARCHAR(120) NOT NULL,
            BF_DESCRIPTION LONGTEXT DEFAULT NULL,
            BF_TYPE VARCHAR(15) NOT NULL,
            BF_CREATED_AT DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            BF_UPDATED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_BENEFIT_CODE (BF_CODE),
            PRIMARY KEY(BF_ID)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE `employee_benefit` (
            EB_ID VARCHAR(16) NOT NULL,
            EB_EMPLOYEE VARCHAR(16) COLLATE utf8mb4_unicode_ci NOT NULL,
            EB_BENEFIT VARCHAR(16) NOT NULL,
            EB_START_DATE DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\',
            EB_END_DATE DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\',
            EB_STATUS VARCHAR(10) NOT NULL,
            EB_CREATED_AT DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            EB_UPDATED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_EB_EMPLOYEE (EB_EMPLOYEE),
            INDEX IDX_EB_BENEFIT (EB_BENEFIT),
            PRIMARY KEY(EB_ID)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE `exit_process` (
            EP_ID VARCHAR(16) NOT NULL,
            EP_EMPLOYEE VARCHAR(16) COLLATE utf8mb4_unicode_ci NOT NULL,
            EP_REASON VARCHAR(20) NOT NULL,
            EP_DEPARTURE_DATE DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\',
            EP_STATUS VARCHAR(15) NOT NULL,
            EP_STARTED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            EP_COMPLETED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            EP_CREATED_AT DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            EP_UPDATED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_EP_EMPLOYEE (EP_EMPLOYEE),
            INDEX IDX_EP_STATUS (EP_STATUS),
            PRIMARY KEY(EP_ID)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE `exit_task` (
            XT_ID VARCHAR(16) NOT NULL,
            XT_PROCESS VARCHAR(16) NOT NULL,
            XT_TITLE VARCHAR(160) NOT NULL,
            XT_TYPE VARCHAR(25) NOT NULL,
            XT_STATUS VARCHAR(15) NOT NULL,
            XT_ASSIGNED_TO VARCHAR(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            XT_DUE_DATE DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\',
            XT_CREATED_AT DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            XT_UPDATED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_XT_PROCESS (XT_PROCESS),
            INDEX IDX_XT_STATUS (XT_STATUS),
            PRIMARY KEY(XT_ID)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE `employee_benefit` ADD CONSTRAINT FK_EB_BENEFIT FOREIGN KEY (EB_BENEFIT) REFERENCES `benefit` (BF_ID)');
        $this->addSql('ALTER TABLE `exit_task` ADD CONSTRAINT FK_XT_PROCESS FOREIGN KEY (XT_PROCESS) REFERENCES `exit_process` (EP_ID)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `exit_task` DROP FOREIGN KEY FK_XT_PROCESS');
        $this->addSql('ALTER TABLE `employee_benefit` DROP FOREIGN KEY FK_EB_BENEFIT');
        $this->addSql('DROP TABLE `exit_task`');
        $this->addSql('DROP TABLE `exit_process`');
        $this->addSql('DROP TABLE `employee_benefit`');
        $this->addSql('DROP TABLE `benefit`');
    }
}
