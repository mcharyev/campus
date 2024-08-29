<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210313124115 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE publication (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, recorder_id INT NOT NULL, title LONGTEXT NOT NULL, publication LONGTEXT DEFAULT NULL, date_published DATE NOT NULL, date_updated DATE DEFAULT NULL, file VARCHAR(255) DEFAULT NULL, INDEX IDX_AF3C6779F675F31B (author_id), INDEX IDX_AF3C677977549ADA (recorder_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE publication ADD CONSTRAINT FK_AF3C6779F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE publication ADD CONSTRAINT FK_AF3C677977549ADA FOREIGN KEY (recorder_id) REFERENCES user (id)');
        $this->addSql('DROP INDEX UNIQ_6DC044C5B3A61235 ON `group`');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6DC044C5B3A61235 ON `group` (group_leader_id)');
        $this->addSql('ALTER TABLE country CHANGE status status SMALLINT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE electronic_library_item CHANGE language_id language_id INT NOT NULL, CHANGE library_unit_id library_unit_id INT NOT NULL, CHANGE publisher publisher VARCHAR(100) NOT NULL, CHANGE date_updated date_updated DATETIME DEFAULT NULL, CHANGE date_created date_created DATETIME NOT NULL');
        $this->addSql('ALTER TABLE electronic_library_item ADD CONSTRAINT FK_FE8153BA82F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)');
        $this->addSql('ALTER TABLE electronic_library_item ADD CONSTRAINT FK_FE8153BA827CC83D FOREIGN KEY (library_unit_id) REFERENCES library_unit (id)');
        $this->addSql('ALTER TABLE electronic_library_item RENAME INDEX idx_b9d4ef7382f1baf4 TO IDX_FE8153BA82F1BAF4');
        $this->addSql('ALTER TABLE electronic_library_item RENAME INDEX idx_b9d4ef73827cc83d TO IDX_FE8153BA827CC83D');
        $this->addSql('ALTER TABLE library_item CHANGE language_id language_id INT NOT NULL, CHANGE library_unit_id library_unit_id INT NOT NULL, CHANGE publisher publisher VARCHAR(100) NOT NULL, CHANGE date_updated date_updated DATETIME DEFAULT NULL, CHANGE date_created date_created DATETIME NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE publication');
        $this->addSql('ALTER TABLE country CHANGE status status SMALLINT DEFAULT 1');
        $this->addSql('ALTER TABLE electronic_library_item DROP FOREIGN KEY FK_FE8153BA82F1BAF4');
        $this->addSql('ALTER TABLE electronic_library_item DROP FOREIGN KEY FK_FE8153BA827CC83D');
        $this->addSql('ALTER TABLE electronic_library_item CHANGE language_id language_id INT DEFAULT 1 NOT NULL, CHANGE library_unit_id library_unit_id INT DEFAULT 1 NOT NULL, CHANGE publisher publisher VARCHAR(100) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE date_updated date_updated DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE date_created date_created DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE electronic_library_item RENAME INDEX idx_fe8153ba827cc83d TO IDX_B9D4EF73827CC83D');
        $this->addSql('ALTER TABLE electronic_library_item RENAME INDEX idx_fe8153ba82f1baf4 TO IDX_B9D4EF7382F1BAF4');
        $this->addSql('DROP INDEX UNIQ_6DC044C5B3A61235 ON `group`');
        $this->addSql('CREATE INDEX UNIQ_6DC044C5B3A61235 ON `group` (group_leader_id)');
        $this->addSql('ALTER TABLE library_item CHANGE language_id language_id INT DEFAULT 1 NOT NULL, CHANGE library_unit_id library_unit_id INT DEFAULT 1 NOT NULL, CHANGE publisher publisher VARCHAR(100) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE date_updated date_updated DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE date_created date_created DATETIME DEFAULT CURRENT_TIMESTAMP');
    }
}
