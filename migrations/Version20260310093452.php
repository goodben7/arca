<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260310093452 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `employee` (EM_ID VARCHAR(16) NOT NULL, EM_EMPLOYEE_NUMBER VARCHAR(30) NOT NULL, EM_FIRST_NAME VARCHAR(80) NOT NULL, EM_LAST_NAME VARCHAR(80) NOT NULL, EM_EMAIL VARCHAR(180) DEFAULT NULL, EM_PHONE VARCHAR(15) DEFAULT NULL, EM_GENDER VARCHAR(10) NOT NULL, EM_BIRTH_DATE DATE DEFAULT NULL, EM_NATIONALITY VARCHAR(60) DEFAULT NULL, EM_MARITAL_STATUS VARCHAR(15) DEFAULT NULL, EM_HIRE_DATE DATE NOT NULL, EM_DEPARTURE_DATE DATE DEFAULT NULL, EM_STATUS VARCHAR(15) NOT NULL, EM_DEPARTMENT VARCHAR(120) DEFAULT NULL, EM_POSITION VARCHAR(120) DEFAULT NULL, EM_MANAGER VARCHAR(16) DEFAULT NULL, EM_CREATED_BY VARCHAR(16) DEFAULT NULL, EM_CREATED_AT DATETIME NOT NULL, EM_UPDATED_AT DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_5D9F75A1F405126B (EM_EMPLOYEE_NUMBER), PRIMARY KEY (EM_ID)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE `employee`');
    }
}
