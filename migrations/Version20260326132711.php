<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260326132711 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE job_offer ADD JO_DESCRIPTION LONGTEXT NOT NULL, CHANGE JO_RECRUITMENT_REQUEST JO_RECRUITMENT_REQUEST VARCHAR(16) DEFAULT NULL');
        $this->addSql('ALTER TABLE recruitment_request ADD RR_DESCRIPTION LONGTEXT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `job_offer` DROP JO_DESCRIPTION, CHANGE JO_RECRUITMENT_REQUEST JO_RECRUITMENT_REQUEST VARCHAR(16) NOT NULL');
        $this->addSql('ALTER TABLE `recruitment_request` DROP RR_DESCRIPTION');
    }
}
