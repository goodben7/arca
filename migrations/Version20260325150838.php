<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260325150838 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `training_request` (TR_ID VARCHAR(16) NOT NULL, TR_DEPARTMENT VARCHAR(16) NOT NULL, TR_REQUESTED_BY VARCHAR(16) NOT NULL, TR_TITLE VARCHAR(160) NOT NULL, TR_DESCRIPTION LONGTEXT NOT NULL, TR_NUMBER_OF_PARTICIPANTS INT NOT NULL, TR_PRIORITY VARCHAR(20) NOT NULL, TR_STATUS VARCHAR(15) NOT NULL, TR_APPROVED_BY VARCHAR(16) DEFAULT NULL, TR_APPROVED_AT DATETIME DEFAULT NULL, TR_REJECTED_BY VARCHAR(16) DEFAULT NULL, TR_REJECTED_AT DATETIME DEFAULT NULL, TR_REJECTION_REASON LONGTEXT DEFAULT NULL, TR_CREATED_AT DATETIME NOT NULL, PRIMARY KEY (TR_ID)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `training_session` (TS_ID VARCHAR(16) NOT NULL, TS_TITLE VARCHAR(160) NOT NULL, TS_TRAINER VARCHAR(160) NOT NULL, TS_START_DATE DATETIME NOT NULL, TS_END_DATE DATETIME NOT NULL, TS_LOCATION VARCHAR(160) NOT NULL, TS_CAPACITY INT NOT NULL, TS_TRAINING_REQUEST VARCHAR(16) NOT NULL, TS_STATUS VARCHAR(15) NOT NULL, TS_STARTED_AT DATETIME DEFAULT NULL, TS_STARTED_BY VARCHAR(16) DEFAULT NULL, TS_COMPLETED_AT DATETIME DEFAULT NULL, TS_COMPLETED_BY VARCHAR(16) DEFAULT NULL, TS_CANCELLED_AT DATETIME DEFAULT NULL, TS_CANCELLED_BY VARCHAR(16) DEFAULT NULL, PRIMARY KEY (TS_ID)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE `training_request`');
        $this->addSql('DROP TABLE `training_session`');
    }
}
