<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260310122038 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `leave_request` (LR_ID VARCHAR(16) NOT NULL, LR_EMPLOYEE VARCHAR(16) NOT NULL, LR_TYPE VARCHAR(10) NOT NULL, LR_START_DATE DATETIME NOT NULL, LR_END_DATE DATETIME NOT NULL, LR_NUMBER_OF_DAYS INT NOT NULL, LR_STATUS VARCHAR(15) NOT NULL, LR_REASON LONGTEXT DEFAULT NULL, LR_APPROVED_BY VARCHAR(16) DEFAULT NULL, LR_CREATED_AT DATETIME NOT NULL, LR_UPDATED_AT DATETIME DEFAULT NULL, PRIMARY KEY (LR_ID)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE `leave_request`');
    }
}
