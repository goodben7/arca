<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260326081304 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `training_enrollment` (TE_ID VARCHAR(16) NOT NULL, TE_EMPLOYEE VARCHAR(16) NOT NULL, TE_TRAINING_SESSION VARCHAR(16) NOT NULL, TE_STATUS VARCHAR(12) NOT NULL, TE_ENROLLED_AT DATETIME DEFAULT NULL, TE_ENROLLED_BY VARCHAR(16) DEFAULT NULL, TE_COMPLETED_AT DATETIME DEFAULT NULL, TE_COMPLETED_BY VARCHAR(16) DEFAULT NULL, TE_ABSENT_AT DATETIME DEFAULT NULL, TE_ABSENT_BY VARCHAR(16) DEFAULT NULL, TE_CREATED_AT DATETIME NOT NULL, PRIMARY KEY (TE_ID)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE `training_enrollment`');
    }
}
