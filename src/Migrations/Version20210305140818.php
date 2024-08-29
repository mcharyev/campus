<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210305140818 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE classroom ADD schedule_name VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE country CHANGE status status SMALLINT DEFAULT 1 NOT NULL');
        $this->addSql('DROP INDEX UNIQ_6DC044C5B3A61235 ON `group`');
        $this->addSql('ALTER TABLE `group` ADD schedule_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6DC044C5B3A61235 ON `group` (group_leader_id)');
        $this->addSql('ALTER TABLE teacher ADD schedule_name VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE library_item CHANGE language_id language_id INT NOT NULL, CHANGE library_unit_id library_unit_id INT NOT NULL, CHANGE publisher publisher VARCHAR(100) NOT NULL, CHANGE date_updated date_updated DATETIME DEFAULT NULL, CHANGE date_created date_created DATETIME NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE classroom DROP schedule_name');
        $this->addSql('ALTER TABLE country CHANGE status status SMALLINT DEFAULT 1');
        $this->addSql('DROP INDEX UNIQ_6DC044C5B3A61235 ON `group`');
        $this->addSql('ALTER TABLE `group` DROP schedule_name');
        $this->addSql('CREATE INDEX UNIQ_6DC044C5B3A61235 ON `group` (group_leader_id)');
        $this->addSql('ALTER TABLE library_item CHANGE language_id language_id INT DEFAULT 1 NOT NULL, CHANGE library_unit_id library_unit_id INT DEFAULT 1 NOT NULL, CHANGE publisher publisher VARCHAR(100) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE date_updated date_updated DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE date_created date_created DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE teacher DROP schedule_name');
    }
}
