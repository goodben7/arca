<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260324093921 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE employee ADD EM_ACTIVATED_AT DATETIME DEFAULT NULL, ADD EM_ACTIVATED_BY VARCHAR(16) DEFAULT NULL, ADD EM_DEACTIVATED_AT DATETIME DEFAULT NULL, ADD EM_DEACTIVATED_BY VARCHAR(16) DEFAULT NULL, ADD EM_ON_LEAVE_AT DATETIME DEFAULT NULL, ADD EM_ON_LEAVE_BY VARCHAR(16) DEFAULT NULL, ADD EM_SUSPENDED_AT DATETIME DEFAULT NULL, ADD EM_SUSPENDED_BY VARCHAR(16) DEFAULT NULL, ADD EM_TERMINATED_AT DATETIME DEFAULT NULL, ADD EM_TERMINATED_BY VARCHAR(16) DEFAULT NULL, ADD EM_RETIRED_AT DATETIME DEFAULT NULL, ADD EM_RETIRED_BY VARCHAR(16) DEFAULT NULL, ADD EM_PROBATION_AT DATETIME DEFAULT NULL, ADD EM_PROBATION_BY VARCHAR(16) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `employee` DROP EM_ACTIVATED_AT, DROP EM_ACTIVATED_BY, DROP EM_DEACTIVATED_AT, DROP EM_DEACTIVATED_BY, DROP EM_ON_LEAVE_AT, DROP EM_ON_LEAVE_BY, DROP EM_SUSPENDED_AT, DROP EM_SUSPENDED_BY, DROP EM_TERMINATED_AT, DROP EM_TERMINATED_BY, DROP EM_RETIRED_AT, DROP EM_RETIRED_BY, DROP EM_PROBATION_AT, DROP EM_PROBATION_BY');
    }
}
