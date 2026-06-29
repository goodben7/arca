<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260702120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add compensation history table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `compensation_history` (
            CH_ID VARCHAR(16) NOT NULL,
            CH_EMPLOYEE VARCHAR(16) COLLATE utf8mb4_unicode_ci NOT NULL,
            CH_OLD_SALARY NUMERIC(14, 2) NOT NULL,
            CH_NEW_SALARY NUMERIC(14, 2) NOT NULL,
            CH_EFFECTIVE_DATE DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\',
            CH_REASON LONGTEXT NOT NULL,
            CH_SOURCE_EVENT VARCHAR(30) NOT NULL,
            CH_CREATED_AT DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_CH_EMPLOYEE (CH_EMPLOYEE),
            INDEX IDX_CH_SOURCE_EVENT (CH_SOURCE_EVENT),
            INDEX IDX_CH_EFFECTIVE_DATE (CH_EFFECTIVE_DATE),
            PRIMARY KEY(CH_ID)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE `compensation_history`');
    }
}
