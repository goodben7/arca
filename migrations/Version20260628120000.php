<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260628120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add onboarding_process and onboarding_task tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `onboarding_process` (
            OP_ID VARCHAR(16) NOT NULL,
            OP_EMPLOYEE VARCHAR(16) NOT NULL,
            OP_STATUS VARCHAR(15) NOT NULL,
            OP_STARTED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            OP_COMPLETED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            OP_CREATED_AT DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            OP_UPDATED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_OP_EMPLOYEE (OP_EMPLOYEE),
            PRIMARY KEY(OP_ID)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE `onboarding_task` (
            OT_ID VARCHAR(16) NOT NULL,
            OT_PROCESS VARCHAR(16) NOT NULL,
            OT_TITLE VARCHAR(160) NOT NULL,
            OT_TYPE VARCHAR(20) NOT NULL,
            OT_STATUS VARCHAR(15) NOT NULL,
            OT_ASSIGNED_TO VARCHAR(16) DEFAULT NULL,
            OT_DUE_DATE DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\',
            OT_CREATED_AT DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            OT_UPDATED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_OT_PROCESS (OT_PROCESS),
            PRIMARY KEY(OT_ID)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE `onboarding_task` ADD CONSTRAINT FK_OT_PROCESS FOREIGN KEY (OT_PROCESS) REFERENCES `onboarding_process` (OP_ID)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `onboarding_task` DROP FOREIGN KEY FK_OT_PROCESS');
        $this->addSql('DROP TABLE `onboarding_task`');
        $this->addSql('DROP TABLE `onboarding_process`');
    }
}
