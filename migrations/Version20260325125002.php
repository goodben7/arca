<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260325125002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `application` (AP_ID VARCHAR(16) NOT NULL, AP_FIRST_NAME VARCHAR(120) NOT NULL, AP_LAST_NAME VARCHAR(120) NOT NULL, AP_GENDER VARCHAR(1) DEFAULT NULL, AP_EMAIL VARCHAR(180) NOT NULL, AP_PHONE VARCHAR(40) NOT NULL, AP_JOB_OFFER VARCHAR(16) NOT NULL, AP_STATUS VARCHAR(15) NOT NULL, AP_APPLIED_AT DATETIME NOT NULL, AP_SHORTLISTED_AT DATETIME DEFAULT NULL, AP_SHORTLISTED_BY VARCHAR(16) DEFAULT NULL, AP_INTERVIEW_AT DATETIME DEFAULT NULL, AP_INTERVIEW_BY VARCHAR(16) DEFAULT NULL, AP_REJECTED_AT DATETIME DEFAULT NULL, AP_REJECTED_BY VARCHAR(16) DEFAULT NULL, AP_REJECTION_REASON LONGTEXT DEFAULT NULL, AP_HIRED_AT DATETIME DEFAULT NULL, AP_HIRED_BY VARCHAR(16) DEFAULT NULL, AP_NOTES LONGTEXT DEFAULT NULL, AP_CREATED_AT DATETIME NOT NULL, PRIMARY KEY (AP_ID)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE `application`');
    }
}
