<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260324140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Contract status audit fields';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `contract` ADD CT_ACTIVATED_AT DATETIME DEFAULT NULL, ADD CT_ACTIVATED_BY VARCHAR(16) DEFAULT NULL, ADD CT_ENDED_AT DATETIME DEFAULT NULL, ADD CT_ENDED_BY VARCHAR(16) DEFAULT NULL, ADD CT_CANCELLED_AT DATETIME DEFAULT NULL, ADD CT_CANCELLED_BY VARCHAR(16) DEFAULT NULL, ADD CT_PENDING_AT DATETIME DEFAULT NULL, ADD CT_PENDING_BY VARCHAR(16) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `contract` DROP CT_ACTIVATED_AT, DROP CT_ACTIVATED_BY, DROP CT_ENDED_AT, DROP CT_ENDED_BY, DROP CT_CANCELLED_AT, DROP CT_CANCELLED_BY, DROP CT_PENDING_AT, DROP CT_PENDING_BY');
    }
}

