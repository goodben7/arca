<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260729160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add document and exit process links to disciplinary_case';
    }

    public function up(Schema $schema): void
    {
        // Match document.DC_ID collation (utf8mb4_0900_ai_ci) so the FK is accepted by MySQL.
        $this->addSql('ALTER TABLE `disciplinary_case` ADD DS_DOCUMENT VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL, ADD DS_EXIT_PROCESS VARCHAR(16) DEFAULT NULL');
        $this->addSql('ALTER TABLE `disciplinary_case` ADD CONSTRAINT FK_DS_DOCUMENT FOREIGN KEY (DS_DOCUMENT) REFERENCES `document` (DC_ID)');
        $this->addSql('ALTER TABLE `disciplinary_case` ADD CONSTRAINT FK_DS_EXIT_PROCESS FOREIGN KEY (DS_EXIT_PROCESS) REFERENCES `exit_process` (EP_ID)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `disciplinary_case` DROP FOREIGN KEY FK_DS_DOCUMENT');
        $this->addSql('ALTER TABLE `disciplinary_case` DROP FOREIGN KEY FK_DS_EXIT_PROCESS');
        $this->addSql('ALTER TABLE `disciplinary_case` DROP DS_DOCUMENT, DROP DS_EXIT_PROCESS');
    }
}
