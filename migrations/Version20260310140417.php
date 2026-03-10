<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260310140417 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `position` (PO_ID VARCHAR(16) NOT NULL, PO_TITLE VARCHAR(120) NOT NULL, PO_DEPARTMENT VARCHAR(120) NOT NULL, PO_LEVEL VARCHAR(15) NOT NULL, PO_DESCRIPTION LONGTEXT DEFAULT NULL, PO_HEADCOUNT INT NOT NULL, PO_OPEN_POSITIONS INT NOT NULL, PO_STATUS VARCHAR(15) NOT NULL, PO_CREATED_AT DATETIME NOT NULL, PO_UPDATED_AT DATETIME DEFAULT NULL, PRIMARY KEY (PO_ID)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE `position`');
    }
}
