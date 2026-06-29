<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260626140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create job architecture referentials: job_family, grade, job_role';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `job_family` (
            JF_ID VARCHAR(16) NOT NULL,
            JF_CODE VARCHAR(40) NOT NULL,
            JF_NAME VARCHAR(120) NOT NULL,
            JF_DESCRIPTION LONGTEXT DEFAULT NULL,
            JF_CREATED_AT DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            JF_UPDATED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_JOB_FAMILY_CODE (JF_CODE),
            PRIMARY KEY(JF_ID)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE `grade` (
            GR_ID VARCHAR(16) NOT NULL,
            GR_CODE VARCHAR(40) NOT NULL,
            GR_NAME VARCHAR(120) NOT NULL,
            GR_RANK INT NOT NULL,
            GR_CREATED_AT DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            GR_UPDATED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_GRADE_CODE (GR_CODE),
            PRIMARY KEY(GR_ID)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE `job_role` (
            JR_ID VARCHAR(16) NOT NULL,
            JR_CODE VARCHAR(40) NOT NULL,
            JR_TITLE VARCHAR(120) NOT NULL,
            JR_JOB_FAMILY VARCHAR(16) NOT NULL,
            JR_GRADE VARCHAR(16) NOT NULL,
            JR_DESCRIPTION LONGTEXT DEFAULT NULL,
            JR_CREATED_AT DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            JR_UPDATED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_JOB_ROLE_CODE (JR_CODE),
            INDEX IDX_JR_JOB_FAMILY (JR_JOB_FAMILY),
            INDEX IDX_JR_GRADE (JR_GRADE),
            PRIMARY KEY(JR_ID)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE `job_role` ADD CONSTRAINT FK_JR_JOB_FAMILY FOREIGN KEY (JR_JOB_FAMILY) REFERENCES `job_family` (JF_ID)');
        $this->addSql('ALTER TABLE `job_role` ADD CONSTRAINT FK_JR_GRADE FOREIGN KEY (JR_GRADE) REFERENCES `grade` (GR_ID)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `job_role` DROP FOREIGN KEY FK_JR_JOB_FAMILY');
        $this->addSql('ALTER TABLE `job_role` DROP FOREIGN KEY FK_JR_GRADE');
        $this->addSql('DROP TABLE `job_role`');
        $this->addSql('DROP TABLE `grade`');
        $this->addSql('DROP TABLE `job_family`');
    }
}
