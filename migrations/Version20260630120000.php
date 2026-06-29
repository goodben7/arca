<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260630120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add training catalog, job role required training, enrich sessions and enrollments';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `training_catalog` (
            TC_ID VARCHAR(16) NOT NULL,
            TC_TITLE VARCHAR(160) NOT NULL,
            TC_DESCRIPTION LONGTEXT DEFAULT NULL,
            TC_PROVIDER VARCHAR(120) NOT NULL,
            TC_DURATION INT NOT NULL,
            TC_COST NUMERIC(10, 2) NOT NULL,
            TC_CREATED_AT DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            TC_UPDATED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_TC_TITLE (TC_TITLE),
            PRIMARY KEY(TC_ID)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE `job_role_required_training` (
            JRT_ID VARCHAR(16) NOT NULL,
            JRT_JOB_ROLE VARCHAR(16) NOT NULL,
            JRT_CATALOG_ITEM VARCHAR(16) NOT NULL,
            JRT_CREATED_AT DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            JRT_UPDATED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_JOB_ROLE_REQUIRED_TRAINING (JRT_JOB_ROLE, JRT_CATALOG_ITEM),
            INDEX IDX_JRT_JOB_ROLE (JRT_JOB_ROLE),
            INDEX IDX_JRT_CATALOG_ITEM (JRT_CATALOG_ITEM),
            PRIMARY KEY(JRT_ID)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE `job_role_required_training` ADD CONSTRAINT FK_JRT_JOB_ROLE FOREIGN KEY (JRT_JOB_ROLE) REFERENCES `job_role` (JR_ID)');
        $this->addSql('ALTER TABLE `job_role_required_training` ADD CONSTRAINT FK_JRT_CATALOG_ITEM FOREIGN KEY (JRT_CATALOG_ITEM) REFERENCES `training_catalog` (TC_ID)');

        $this->addSql('ALTER TABLE `training_session` ADD TS_CATALOG_ITEM VARCHAR(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL');
        $this->addSql('ALTER TABLE `training_session` ADD CONSTRAINT FK_TS_CATALOG_ITEM FOREIGN KEY (TS_CATALOG_ITEM) REFERENCES `training_catalog` (TC_ID)');
        $this->addSql('CREATE INDEX IDX_TS_CATALOG_ITEM ON `training_session` (TS_CATALOG_ITEM)');

        $this->addSql('UPDATE `training_enrollment` SET TE_STATUS = \'ASSIGNED\' WHERE TE_STATUS = \'ENROLLED\'');

        $this->addSql('ALTER TABLE `training_enrollment`
            CHANGE TE_ENROLLED_AT TE_ASSIGNED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            CHANGE TE_ENROLLED_BY TE_ASSIGNED_BY VARCHAR(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            CHANGE TE_STATUS TE_STATUS VARCHAR(15) NOT NULL');

        $this->addSql('ALTER TABLE `training_enrollment`
            ADD TE_SCORE NUMERIC(5, 2) DEFAULT NULL,
            ADD TE_CERTIFICATE VARCHAR(255) DEFAULT NULL,
            ADD TE_STARTED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            ADD TE_STARTED_BY VARCHAR(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            ADD TE_CERTIFIED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            ADD TE_CERTIFIED_BY VARCHAR(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `training_enrollment`
            DROP TE_SCORE,
            DROP TE_CERTIFICATE,
            DROP TE_STARTED_AT,
            DROP TE_STARTED_BY,
            DROP TE_CERTIFIED_AT,
            DROP TE_CERTIFIED_BY');

        $this->addSql('ALTER TABLE `training_enrollment`
            CHANGE TE_ASSIGNED_AT TE_ENROLLED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            CHANGE TE_ASSIGNED_BY TE_ENROLLED_BY VARCHAR(16) DEFAULT NULL,
            CHANGE TE_STATUS TE_STATUS VARCHAR(12) NOT NULL');

        $this->addSql('UPDATE `training_enrollment` SET TE_STATUS = \'ENROLLED\' WHERE TE_STATUS = \'ASSIGNED\'');

        $this->addSql('ALTER TABLE `training_session` DROP FOREIGN KEY FK_TS_CATALOG_ITEM');
        $this->addSql('DROP INDEX IDX_TS_CATALOG_ITEM ON `training_session`');
        $this->addSql('ALTER TABLE `training_session` DROP TS_CATALOG_ITEM');

        $this->addSql('ALTER TABLE `job_role_required_training` DROP FOREIGN KEY FK_JRT_JOB_ROLE');
        $this->addSql('ALTER TABLE `job_role_required_training` DROP FOREIGN KEY FK_JRT_CATALOG_ITEM');
        $this->addSql('DROP TABLE `job_role_required_training`');
        $this->addSql('DROP TABLE `training_catalog`');
    }
}
