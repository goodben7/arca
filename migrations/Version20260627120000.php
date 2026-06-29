<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260627120000 extends AbstractMigration
{
    private const string LIBRE_CATEGORY_ID = 'SKCLIBRE06271200';

    private int $skillIdSequence = 0;

    public function getDescription(): string
    {
        return 'Refactor skills: skill_category + skill catalog + employee_skill with data migration';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `skill_category` (
            SKC_ID VARCHAR(16) NOT NULL,
            SKC_CODE VARCHAR(40) NOT NULL,
            SKC_NAME VARCHAR(120) NOT NULL,
            SKC_DESCRIPTION LONGTEXT DEFAULT NULL,
            SKC_CREATED_AT DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            SKC_UPDATED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_SKILL_CATEGORY_CODE (SKC_CODE),
            PRIMARY KEY(SKC_ID)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('RENAME TABLE `skill` TO `employee_skill`');

        $this->addSql('ALTER TABLE `employee_skill`
            CHANGE SK_ID ES_ID VARCHAR(16) NOT NULL,
            CHANGE SK_EMPLOYEE ES_EMPLOYEE VARCHAR(16) NOT NULL,
            CHANGE SK_NAME ES_NAME VARCHAR(120) NOT NULL,
            CHANGE SK_LEVEL ES_LEVEL VARCHAR(15) NOT NULL,
            CHANGE SK_CREATED_AT ES_CREATED_AT DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            CHANGE SK_UPDATED_AT ES_UPDATED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');

        $this->addSql('CREATE TABLE `skill` (
            SK_ID VARCHAR(16) NOT NULL,
            SK_CODE VARCHAR(40) NOT NULL,
            SK_NAME VARCHAR(120) NOT NULL,
            SK_CATEGORY VARCHAR(16) NOT NULL,
            SK_DESCRIPTION LONGTEXT DEFAULT NULL,
            SK_CREATED_AT DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            SK_UPDATED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_SKILL_CODE (SK_CODE),
            INDEX IDX_SK_CATEGORY (SK_CATEGORY),
            PRIMARY KEY(SK_ID)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE `skill` ADD CONSTRAINT FK_SK_CATEGORY FOREIGN KEY (SK_CATEGORY) REFERENCES `skill_category` (SKC_ID)');

        $this->addSql(sprintf(
            'INSERT INTO `skill_category` (SKC_ID, SKC_CODE, SKC_NAME, SKC_DESCRIPTION, SKC_CREATED_AT) VALUES (\'%s\', \'LIBRE\', \'Compétences libres\', \'Compétences saisies librement avant migration catalogue\', NOW())',
            self::LIBRE_CATEGORY_ID,
        ));

        $this->addSql('ALTER TABLE `employee_skill` ADD ES_SKILL VARCHAR(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL, ADD ES_VALIDATED_AT DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function postUp(Schema $schema): void
    {
        $this->migrateFreeSkillsToCatalog();

        $this->connection->executeStatement('ALTER TABLE `employee_skill` DROP ES_NAME');
        $this->connection->executeStatement('ALTER TABLE `employee_skill` MODIFY ES_SKILL VARCHAR(16) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->connection->executeStatement('ALTER TABLE `employee_skill` MODIFY ES_EMPLOYEE VARCHAR(16) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->connection->executeStatement('ALTER TABLE `employee_skill` ADD CONSTRAINT FK_ES_SKILL FOREIGN KEY (ES_SKILL) REFERENCES `skill` (SK_ID)');
        $this->connection->executeStatement('CREATE INDEX IDX_ES_SKILL ON `employee_skill` (ES_SKILL)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `employee_skill` DROP FOREIGN KEY FK_ES_SKILL');
        $this->addSql('DROP INDEX IDX_ES_SKILL ON `employee_skill`');

        $this->addSql('ALTER TABLE `employee_skill` ADD ES_NAME VARCHAR(120) NOT NULL');

        $this->addSql('ALTER TABLE `employee_skill` DROP ES_SKILL, DROP ES_VALIDATED_AT');

        $this->addSql('DROP TABLE `skill`');
        $this->addSql('DROP TABLE `skill_category`');

        $this->addSql('ALTER TABLE `employee_skill`
            CHANGE ES_ID SK_ID VARCHAR(16) NOT NULL,
            CHANGE ES_EMPLOYEE SK_EMPLOYEE VARCHAR(16) NOT NULL,
            CHANGE ES_LEVEL SK_LEVEL VARCHAR(15) NOT NULL,
            CHANGE ES_CREATED_AT SK_CREATED_AT DATETIME NOT NULL,
            CHANGE ES_UPDATED_AT SK_UPDATED_AT DATETIME DEFAULT NULL');

        $this->addSql('RENAME TABLE `employee_skill` TO `skill`');
    }

    public function postDown(Schema $schema): void
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT es.ES_ID, s.SK_NAME
             FROM `employee_skill` es
             INNER JOIN `skill` s ON s.SK_ID = es.ES_SKILL'
        );

        foreach ($rows as $row) {
            $this->connection->executeStatement(
                'UPDATE `employee_skill` SET ES_NAME = ? WHERE ES_ID = ?',
                [$row['SK_NAME'], $row['ES_ID']],
            );
        }
    }

    private function migrateFreeSkillsToCatalog(): void
    {
        $names = $this->connection->fetchFirstColumn(
            'SELECT DISTINCT ES_NAME FROM `employee_skill` ORDER BY ES_NAME'
        );

        $usedCodes = [];

        foreach ($names as $name) {
            $code = $this->buildUniqueSkillCode((string) $name, $usedCodes);
            $skillId = $this->generateSkillId();

            $this->connection->executeStatement(
                'INSERT INTO `skill` (SK_ID, SK_CODE, SK_NAME, SK_CATEGORY, SK_DESCRIPTION, SK_CREATED_AT)
                 VALUES (?, ?, ?, ?, NULL, NOW())',
                [$skillId, $code, $name, self::LIBRE_CATEGORY_ID],
            );

            $this->connection->executeStatement(
                'UPDATE `employee_skill` SET ES_SKILL = ? WHERE ES_NAME = ?',
                [$skillId, $name],
            );
        }
    }

    /**
     * @param array<string, true> $usedCodes
     */
    private function buildUniqueSkillCode(string $name, array &$usedCodes): string
    {
        $base = strtoupper(trim((string) preg_replace('/_+/', '_', preg_replace('/[^A-Za-z0-9]+/', '_', $name)), '_'));

        if ('' === $base) {
            $base = 'FREE_SKILL';
        }

        $base = substr($base, 0, 36);
        $code = $base;
        $suffix = 2;

        while (isset($usedCodes[$code])) {
            $suffixStr = '_'.$suffix;
            $code = substr($base, 0, 40 - strlen($suffixStr)).$suffixStr;
            ++$suffix;
        }

        $usedCodes[$code] = true;

        return $code;
    }

    private function generateSkillId(): string
    {
        ++$this->skillIdSequence;

        return sprintf('SKM%013d', $this->skillIdSequence);
    }
}
