<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260701120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add career plan and mobility request tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `career_plan` (
            PL_ID VARCHAR(16) NOT NULL,
            PL_EMPLOYEE VARCHAR(16) NOT NULL,
            PL_TARGET_JOB_ROLE VARCHAR(16) NOT NULL,
            PL_TARGET_GRADE VARCHAR(16) DEFAULT NULL,
            PL_TARGET_DATE DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\',
            PL_STATUS VARCHAR(15) NOT NULL,
            PL_NOTES LONGTEXT DEFAULT NULL,
            PL_CREATED_AT DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PL_UPDATED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_PL_EMPLOYEE (PL_EMPLOYEE),
            INDEX IDX_PL_TARGET_JOB_ROLE (PL_TARGET_JOB_ROLE),
            INDEX IDX_PL_TARGET_GRADE (PL_TARGET_GRADE),
            INDEX IDX_PL_STATUS (PL_STATUS),
            PRIMARY KEY(PL_ID)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE `mobility_request` (
            MB_ID VARCHAR(16) NOT NULL,
            MB_EMPLOYEE VARCHAR(16) COLLATE utf8mb4_unicode_ci NOT NULL,
            MB_TYPE VARCHAR(12) NOT NULL,
            MB_STATUS VARCHAR(20) NOT NULL,
            MB_TARGET_JOB_ROLE VARCHAR(16) DEFAULT NULL,
            MB_TARGET_GRADE VARCHAR(16) DEFAULT NULL,
            MB_TARGET_DEPARTMENT VARCHAR(120) DEFAULT NULL,
            MB_REASON LONGTEXT DEFAULT NULL,
            MB_SUBMITTED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            MB_SUBMITTED_BY VARCHAR(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            MB_MANAGER_APPROVED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            MB_MANAGER_APPROVED_BY VARCHAR(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            MB_HR_APPROVED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            MB_HR_APPROVED_BY VARCHAR(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            MB_EXECUTIVE_APPROVED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            MB_EXECUTIVE_APPROVED_BY VARCHAR(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            MB_IMPLEMENTED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            MB_IMPLEMENTED_BY VARCHAR(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            MB_REJECTED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            MB_REJECTED_BY VARCHAR(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            MB_REJECTION_REASON LONGTEXT DEFAULT NULL,
            MB_CANCELLED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            MB_CANCELLED_BY VARCHAR(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            MB_CREATED_AT DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            MB_UPDATED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_MB_EMPLOYEE (MB_EMPLOYEE),
            INDEX IDX_MB_TYPE (MB_TYPE),
            INDEX IDX_MB_STATUS (MB_STATUS),
            INDEX IDX_MB_TARGET_JOB_ROLE (MB_TARGET_JOB_ROLE),
            INDEX IDX_MB_TARGET_GRADE (MB_TARGET_GRADE),
            PRIMARY KEY(MB_ID)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE `career_plan` ADD CONSTRAINT FK_PL_TARGET_JOB_ROLE FOREIGN KEY (PL_TARGET_JOB_ROLE) REFERENCES `job_role` (JR_ID)');
        $this->addSql('ALTER TABLE `career_plan` ADD CONSTRAINT FK_PL_TARGET_GRADE FOREIGN KEY (PL_TARGET_GRADE) REFERENCES `grade` (GR_ID)');

        $this->addSql('ALTER TABLE `mobility_request` ADD CONSTRAINT FK_MB_TARGET_JOB_ROLE FOREIGN KEY (MB_TARGET_JOB_ROLE) REFERENCES `job_role` (JR_ID)');
        $this->addSql('ALTER TABLE `mobility_request` ADD CONSTRAINT FK_MB_TARGET_GRADE FOREIGN KEY (MB_TARGET_GRADE) REFERENCES `grade` (GR_ID)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `mobility_request` DROP FOREIGN KEY FK_MB_TARGET_JOB_ROLE');
        $this->addSql('ALTER TABLE `mobility_request` DROP FOREIGN KEY FK_MB_TARGET_GRADE');
        $this->addSql('ALTER TABLE `career_plan` DROP FOREIGN KEY FK_PL_TARGET_JOB_ROLE');
        $this->addSql('ALTER TABLE `career_plan` DROP FOREIGN KEY FK_PL_TARGET_GRADE');
        $this->addSql('DROP TABLE `mobility_request`');
        $this->addSql('DROP TABLE `career_plan`');
    }
}
