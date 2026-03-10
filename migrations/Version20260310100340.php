<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260310100340 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `skill` (SK_ID VARCHAR(16) NOT NULL, SK_EMPLOYEE VARCHAR(16) NOT NULL, SK_NAME VARCHAR(120) NOT NULL, SK_LEVEL VARCHAR(15) NOT NULL, SK_CREATED_AT DATETIME NOT NULL, SK_UPDATED_AT DATETIME DEFAULT NULL, PRIMARY KEY (SK_ID)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `work_experience` (WE_ID VARCHAR(16) NOT NULL, WE_EMPLOYEE VARCHAR(16) NOT NULL, WE_COMPANY VARCHAR(180) NOT NULL, WE_POSITION VARCHAR(120) NOT NULL, WE_START_DATE DATETIME NOT NULL, WE_END_DATE DATETIME DEFAULT NULL, WE_DESCRIPTION LONGTEXT DEFAULT NULL, WE_IS_INTERNAL TINYINT DEFAULT 0 NOT NULL, PRIMARY KEY (WE_ID)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE `skill`');
        $this->addSql('DROP TABLE `work_experience`');
    }
}
