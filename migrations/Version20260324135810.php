<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260324135810 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE position ADD PO_OPENED_AT DATETIME DEFAULT NULL, ADD PO_OPENED_BY VARCHAR(16) DEFAULT NULL, ADD PO_CLOSED_AT DATETIME DEFAULT NULL, ADD PO_CLOSED_BY VARCHAR(16) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `position` DROP PO_OPENED_AT, DROP PO_OPENED_BY, DROP PO_CLOSED_AT, DROP PO_CLOSED_BY');
    }
}
