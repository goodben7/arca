<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260310104928 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `department` (DP_ID VARCHAR(16) NOT NULL, DP_NAME VARCHAR(120) NOT NULL, DP_CODE VARCHAR(40) NOT NULL, DP_DESCRIPTION LONGTEXT DEFAULT NULL, DP_MANAGER_ID VARCHAR(16) DEFAULT NULL, DP_CREATED_AT DATETIME NOT NULL, DP_UPDATED_AT DATETIME DEFAULT NULL, PRIMARY KEY (DP_ID)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE `department`');
    }
}
