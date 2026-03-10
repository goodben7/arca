<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260310112027 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `contract` (CT_ID VARCHAR(16) NOT NULL, CT_EMPLOYEE VARCHAR(16) NOT NULL, CT_TYPE VARCHAR(40) NOT NULL, CT_START_DATE DATETIME NOT NULL, CT_END_DATE DATETIME DEFAULT NULL, CT_SALARY NUMERIC(14, 2) NOT NULL, CT_STATUS VARCHAR(15) NOT NULL, CT_CREATED_AT DATETIME NOT NULL, CT_UPDATED_AT DATETIME DEFAULT NULL, PRIMARY KEY (CT_ID)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE `contract`');
    }
}
