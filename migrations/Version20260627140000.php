<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260627140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add job_role_required_skill and unique employee_skill assignment';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `job_role_required_skill` (
            JRS_ID VARCHAR(16) NOT NULL,
            JRS_JOB_ROLE VARCHAR(16) NOT NULL,
            JRS_SKILL VARCHAR(16) NOT NULL,
            JRS_MINIMUM_LEVEL VARCHAR(15) NOT NULL,
            JRS_CREATED_AT DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            JRS_UPDATED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_JOB_ROLE_REQUIRED_SKILL (JRS_JOB_ROLE, JRS_SKILL),
            INDEX IDX_JRS_JOB_ROLE (JRS_JOB_ROLE),
            INDEX IDX_JRS_SKILL (JRS_SKILL),
            PRIMARY KEY(JRS_ID)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE `job_role_required_skill` ADD CONSTRAINT FK_JRS_JOB_ROLE FOREIGN KEY (JRS_JOB_ROLE) REFERENCES `job_role` (JR_ID)');
        $this->addSql('ALTER TABLE `job_role_required_skill` ADD CONSTRAINT FK_JRS_SKILL FOREIGN KEY (JRS_SKILL) REFERENCES `skill` (SK_ID)');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_EMPLOYEE_SKILL ON `employee_skill` (ES_EMPLOYEE, ES_SKILL)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_EMPLOYEE_SKILL ON `employee_skill`');
        $this->addSql('ALTER TABLE `job_role_required_skill` DROP FOREIGN KEY FK_JRS_JOB_ROLE');
        $this->addSql('ALTER TABLE `job_role_required_skill` DROP FOREIGN KEY FK_JRS_SKILL');
        $this->addSql('DROP TABLE `job_role_required_skill`');
    }
}
