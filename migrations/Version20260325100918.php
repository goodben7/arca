<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260325100918 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `job_offer` (JO_ID VARCHAR(16) NOT NULL, JO_TITLE VARCHAR(120) NOT NULL, JO_DEPARTMENT VARCHAR(16) NOT NULL, JO_RECRUITMENT_REQUEST VARCHAR(16) NOT NULL, JO_STATUS VARCHAR(15) NOT NULL, JO_PUBLISHED_AT DATETIME DEFAULT NULL, JO_PUBLISHED_BY VARCHAR(16) DEFAULT NULL, JO_CLOSED_AT DATETIME DEFAULT NULL, JO_CLOSED_BY VARCHAR(16) DEFAULT NULL, JO_CREATED_AT DATETIME NOT NULL, PRIMARY KEY (JO_ID)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `recruitment_request` (RR_ID VARCHAR(16) NOT NULL, RR_DEPARTMENT VARCHAR(16) NOT NULL, RR_REQUESTED_BY VARCHAR(16) NOT NULL, RR_POSITION VARCHAR(16) NOT NULL, RR_NUMBER_OF_POSITIONS INT NOT NULL, RR_JUSTIFICATION LONGTEXT NOT NULL, RR_STATUS VARCHAR(15) NOT NULL, RR_APPROVED_BY VARCHAR(16) DEFAULT NULL, RR_APPROVED_AT DATETIME DEFAULT NULL, RR_REJECTED_BY VARCHAR(16) DEFAULT NULL, RR_REJECTED_AT DATETIME DEFAULT NULL, RR_REJECTION_REASON LONGTEXT DEFAULT NULL, RR_CREATED_AT DATETIME NOT NULL, PRIMARY KEY (RR_ID)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE `job_offer`');
        $this->addSql('DROP TABLE `recruitment_request`');
    }
}
