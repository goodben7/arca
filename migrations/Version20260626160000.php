<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260626160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Link employee to job_role and grade';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `employee` ADD EM_JOB_ROLE VARCHAR(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL, ADD EM_GRADE VARCHAR(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL');
        $this->addSql('ALTER TABLE `employee` ADD CONSTRAINT FK_EM_JOB_ROLE FOREIGN KEY (EM_JOB_ROLE) REFERENCES `job_role` (JR_ID)');
        $this->addSql('ALTER TABLE `employee` ADD CONSTRAINT FK_EM_GRADE FOREIGN KEY (EM_GRADE) REFERENCES `grade` (GR_ID)');
        $this->addSql('CREATE INDEX IDX_EM_JOB_ROLE ON `employee` (EM_JOB_ROLE)');
        $this->addSql('CREATE INDEX IDX_EM_GRADE ON `employee` (EM_GRADE)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `employee` DROP FOREIGN KEY FK_EM_JOB_ROLE');
        $this->addSql('ALTER TABLE `employee` DROP FOREIGN KEY FK_EM_GRADE');
        $this->addSql('DROP INDEX IDX_EM_JOB_ROLE ON `employee`');
        $this->addSql('DROP INDEX IDX_EM_GRADE ON `employee`');
        $this->addSql('ALTER TABLE `employee` DROP EM_JOB_ROLE, DROP EM_GRADE');
    }
}
