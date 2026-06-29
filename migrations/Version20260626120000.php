<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260626120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create employee_journey_entry table';
    }

    public function up(Schema $schema): void
    {
        // Align employee PK collation with newer referential tables (utf8mb4_unicode_ci).
        $this->addSql('ALTER TABLE `employee` MODIFY EM_ID VARCHAR(16) NOT NULL COLLATE utf8mb4_unicode_ci');

        $this->addSql('CREATE TABLE `employee_journey_entry` (
            EJ_ID VARCHAR(16) NOT NULL,
            EJ_EMPLOYEE VARCHAR(16) NOT NULL,
            EJ_STAGE VARCHAR(30) NOT NULL,
            EJ_EVENT_TYPE VARCHAR(40) NOT NULL,
            EJ_SOURCE_ENTITY_TYPE VARCHAR(40) DEFAULT NULL,
            EJ_SOURCE_ENTITY_ID VARCHAR(16) DEFAULT NULL,
            EJ_METADATA JSON DEFAULT NULL,
            EJ_OCCURRED_AT DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            EJ_ACTOR_ID VARCHAR(16) DEFAULT NULL,
            EJ_DESCRIPTION VARCHAR(255) DEFAULT NULL,
            INDEX IDX_EJ_EMPLOYEE_OCCURRED_AT (EJ_EMPLOYEE, EJ_OCCURRED_AT),
            PRIMARY KEY(EJ_ID)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `employee_journey_entry` ADD CONSTRAINT FK_EJ_EMPLOYEE FOREIGN KEY (EJ_EMPLOYEE) REFERENCES `employee` (EM_ID)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `employee_journey_entry` DROP FOREIGN KEY FK_EJ_EMPLOYEE');
        $this->addSql('DROP TABLE `employee_journey_entry`');
    }
}
