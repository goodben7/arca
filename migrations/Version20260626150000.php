<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260626150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add career_path and link recruitment entities to job_role';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `career_path` (
            CP_ID VARCHAR(16) NOT NULL,
            CP_FROM_JOB_ROLE VARCHAR(16) NOT NULL,
            CP_TO_JOB_ROLE VARCHAR(16) NOT NULL,
            CP_CONDITIONS JSON DEFAULT NULL,
            CP_CREATED_AT DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            CP_UPDATED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_CAREER_PATH_TRANSITION (CP_FROM_JOB_ROLE, CP_TO_JOB_ROLE),
            INDEX IDX_CP_FROM_JOB_ROLE (CP_FROM_JOB_ROLE),
            INDEX IDX_CP_TO_JOB_ROLE (CP_TO_JOB_ROLE),
            PRIMARY KEY(CP_ID)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE `career_path` ADD CONSTRAINT FK_CP_FROM_JOB_ROLE FOREIGN KEY (CP_FROM_JOB_ROLE) REFERENCES `job_role` (JR_ID)');
        $this->addSql('ALTER TABLE `career_path` ADD CONSTRAINT FK_CP_TO_JOB_ROLE FOREIGN KEY (CP_TO_JOB_ROLE) REFERENCES `job_role` (JR_ID)');

        $this->addSql('ALTER TABLE `job_offer` ADD JO_JOB_ROLE VARCHAR(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL');
        $this->addSql('ALTER TABLE `job_offer` ADD CONSTRAINT FK_JO_JOB_ROLE FOREIGN KEY (JO_JOB_ROLE) REFERENCES `job_role` (JR_ID)');
        $this->addSql('CREATE INDEX IDX_JO_JOB_ROLE ON `job_offer` (JO_JOB_ROLE)');

        $this->addSql('ALTER TABLE `recruitment_request` ADD RR_JOB_ROLE VARCHAR(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL');
        $this->addSql('ALTER TABLE `recruitment_request` ADD CONSTRAINT FK_RR_JOB_ROLE FOREIGN KEY (RR_JOB_ROLE) REFERENCES `job_role` (JR_ID)');
        $this->addSql('CREATE INDEX IDX_RR_JOB_ROLE ON `recruitment_request` (RR_JOB_ROLE)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `recruitment_request` DROP FOREIGN KEY FK_RR_JOB_ROLE');
        $this->addSql('DROP INDEX IDX_RR_JOB_ROLE ON `recruitment_request`');
        $this->addSql('ALTER TABLE `recruitment_request` DROP RR_JOB_ROLE');

        $this->addSql('ALTER TABLE `job_offer` DROP FOREIGN KEY FK_JO_JOB_ROLE');
        $this->addSql('DROP INDEX IDX_JO_JOB_ROLE ON `job_offer`');
        $this->addSql('ALTER TABLE `job_offer` DROP JO_JOB_ROLE');

        $this->addSql('ALTER TABLE `career_path` DROP FOREIGN KEY FK_CP_FROM_JOB_ROLE');
        $this->addSql('ALTER TABLE `career_path` DROP FOREIGN KEY FK_CP_TO_JOB_ROLE');
        $this->addSql('DROP TABLE `career_path`');
    }
}
