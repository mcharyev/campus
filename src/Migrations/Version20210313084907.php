<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210313084907 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE electronic_library_item (id INT AUTO_INCREMENT NOT NULL, language_id INT NOT NULL, library_unit_id INT NOT NULL, type SMALLINT DEFAULT 1 NOT NULL, main_title LONGTEXT NOT NULL, secondary_title LONGTEXT DEFAULT NULL, author LONGTEXT DEFAULT NULL, writer_number VARCHAR(10) DEFAULT NULL, year SMALLINT DEFAULT 0 NOT NULL, edition SMALLINT DEFAULT 1 NOT NULL, number VARCHAR(20) DEFAULT \'1\' NOT NULL, volume VARCHAR(20) DEFAULT \'1\', copy_number SMALLINT DEFAULT NULL, call_number VARCHAR(15) DEFAULT NULL, call_number_original VARCHAR(15) DEFAULT NULL, isbn VARCHAR(13) DEFAULT NULL, uok VARCHAR(20) DEFAULT NULL, status SMALLINT DEFAULT 1 NOT NULL, publisher VARCHAR(100) NOT NULL, place VARCHAR(50) DEFAULT NULL, price DOUBLE PRECISION DEFAULT \'0\' NOT NULL, invoice VARCHAR(100) DEFAULT NULL, date_updated DATETIME DEFAULT NULL, date_created DATETIME NOT NULL, INDEX IDX_FE8153BA82F1BAF4 (language_id), INDEX IDX_FE8153BA827CC83D (library_unit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE electronic_library_item ADD CONSTRAINT FK_FE8153BA82F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)');
        $this->addSql('ALTER TABLE electronic_library_item ADD CONSTRAINT FK_FE8153BA827CC83D FOREIGN KEY (library_unit_id) REFERENCES library_unit (id)');
        $this->addSql('DROP INDEX UNIQ_6DC044C5B3A61235 ON `group`');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6DC044C5B3A61235 ON `group` (group_leader_id)');
        $this->addSql('ALTER TABLE country CHANGE status status SMALLINT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE library_item CHANGE language_id language_id INT NOT NULL, CHANGE library_unit_id library_unit_id INT NOT NULL, CHANGE publisher publisher VARCHAR(100) NOT NULL, CHANGE date_updated date_updated DATETIME DEFAULT NULL, CHANGE date_created date_created DATETIME NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE electronic_library_item');
        $this->addSql('ALTER TABLE country CHANGE status status SMALLINT DEFAULT 1');
        $this->addSql('DROP INDEX UNIQ_6DC044C5B3A61235 ON `group`');
        $this->addSql('CREATE INDEX UNIQ_6DC044C5B3A61235 ON `group` (group_leader_id)');
        $this->addSql('ALTER TABLE library_item CHANGE language_id language_id INT DEFAULT 1 NOT NULL, CHANGE library_unit_id library_unit_id INT DEFAULT 1 NOT NULL, CHANGE publisher publisher VARCHAR(100) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE date_updated date_updated DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE date_created date_created DATETIME DEFAULT CURRENT_TIMESTAMP');
    }
}
