<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260704120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add succession plans for critical job roles';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `succession_plan` (
            SP_ID VARCHAR(16) NOT NULL,
            SP_CRITICAL_JOB_ROLE VARCHAR(16) NOT NULL,
            SP_CANDIDATE VARCHAR(16) COLLATE utf8mb4_unicode_ci NOT NULL,
            SP_READINESS_LEVEL VARCHAR(25) NOT NULL,
            SP_STATUS VARCHAR(10) NOT NULL,
            SP_NOTES LONGTEXT DEFAULT NULL,
            SP_CREATED_AT DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            SP_UPDATED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_SP_CRITICAL_JOB_ROLE (SP_CRITICAL_JOB_ROLE),
            INDEX IDX_SP_CANDIDATE (SP_CANDIDATE),
            INDEX IDX_SP_STATUS (SP_STATUS),
            UNIQUE INDEX UNIQ_SP_CRITICAL_CANDIDATE (SP_CRITICAL_JOB_ROLE, SP_CANDIDATE),
            PRIMARY KEY(SP_ID)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE `succession_plan` ADD CONSTRAINT FK_SP_CRITICAL_JOB_ROLE FOREIGN KEY (SP_CRITICAL_JOB_ROLE) REFERENCES `job_role` (JR_ID)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `succession_plan` DROP FOREIGN KEY FK_SP_CRITICAL_JOB_ROLE');
        $this->addSql('DROP TABLE `succession_plan`');
    }
}
