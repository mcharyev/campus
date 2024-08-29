<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211023102158 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE country CHANGE status status SMALLINT DEFAULT 1 NOT NULL');
        $this->addSql('DROP INDEX UNIQ_6DC044C5B3A61235 ON `group`');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6DC044C5B3A61235 ON `group` (group_leader_id)');
        $this->addSql('ALTER TABLE program_course ADD credit_type SMALLINT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE teacher_attendance ADD CONSTRAINT FK_E7FCAF31591CC992 FOREIGN KEY (course_id) REFERENCES taught_course (id)');
        $this->addSql('ALTER TABLE teacher_attendance ADD CONSTRAINT FK_E7FCAF3141807E1D FOREIGN KEY (teacher_id) REFERENCES teacher (id)');
        $this->addSql('ALTER TABLE teacher_attendance ADD CONSTRAINT FK_E7FCAF3139EB6F FOREIGN KEY (class_type_id) REFERENCES class_type (id)');
        $this->addSql('ALTER TABLE teacher_attendance ADD CONSTRAINT FK_E7FCAF31AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('ALTER TABLE teacher_attendance ADD CONSTRAINT FK_E7FCAF31680CAB68 FOREIGN KEY (faculty_id) REFERENCES faculty (id)');
        $this->addSql('ALTER TABLE teacher_work_item ADD CONSTRAINT FK_9CB3D83D3CD988BE FOREIGN KEY (department_work_item_id) REFERENCES department_work_item (id)');
        $this->addSql('ALTER TABLE teacher_work_item ADD CONSTRAINT FK_9CB3D83D96CA1090 FOREIGN KEY (taught_course_id) REFERENCES taught_course (id)');
        $this->addSql('ALTER TABLE teacher_work_item ADD CONSTRAINT FK_9CB3D83D41807E1D FOREIGN KEY (teacher_id) REFERENCES teacher (id)');
        $this->addSql('ALTER TABLE teacher_work_item ADD CONSTRAINT FK_9CB3D83DAE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('ALTER TABLE teacher_work_item ADD CONSTRAINT FK_9CB3D83D6D5E7770 FOREIGN KEY (teacher_work_set_id) REFERENCES teacher_work_set (id)');
        $this->addSql('ALTER TABLE teacher_work_set ADD CONSTRAINT FK_9D6CC1CB41807E1D FOREIGN KEY (teacher_id) REFERENCES teacher (id)');
        $this->addSql('ALTER TABLE teacher_work_set ADD CONSTRAINT FK_9D6CC1CBAE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
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

        $this->addSql('ALTER TABLE country CHANGE status status SMALLINT DEFAULT 1');
        $this->addSql('ALTER TABLE electronic_library_item DROP FOREIGN KEY FK_FE8153BA82F1BAF4');
        $this->addSql('ALTER TABLE electronic_library_item DROP FOREIGN KEY FK_FE8153BA827CC83D');
        $this->addSql('ALTER TABLE electronic_library_item CHANGE language_id language_id INT DEFAULT 1 NOT NULL, CHANGE library_unit_id library_unit_id INT DEFAULT 1 NOT NULL, CHANGE publisher publisher VARCHAR(100) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE date_updated date_updated DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE date_created date_created DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE electronic_library_item RENAME INDEX idx_fe8153ba827cc83d TO IDX_B9D4EF73827CC83D');
        $this->addSql('ALTER TABLE electronic_library_item RENAME INDEX idx_fe8153ba82f1baf4 TO IDX_B9D4EF7382F1BAF4');
        $this->addSql('DROP INDEX UNIQ_6DC044C5B3A61235 ON `group`');
        $this->addSql('CREATE INDEX UNIQ_6DC044C5B3A61235 ON `group` (group_leader_id)');
        $this->addSql('ALTER TABLE library_item CHANGE language_id language_id INT DEFAULT 1 NOT NULL, CHANGE library_unit_id library_unit_id INT DEFAULT 1 NOT NULL, CHANGE publisher publisher VARCHAR(100) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE date_updated date_updated DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE date_created date_created DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE program_course DROP credit_type');
        $this->addSql('ALTER TABLE teacher_attendance DROP FOREIGN KEY FK_E7FCAF31591CC992');
        $this->addSql('ALTER TABLE teacher_attendance DROP FOREIGN KEY FK_E7FCAF3141807E1D');
        $this->addSql('ALTER TABLE teacher_attendance DROP FOREIGN KEY FK_E7FCAF3139EB6F');
        $this->addSql('ALTER TABLE teacher_attendance DROP FOREIGN KEY FK_E7FCAF31AE80F5DF');
        $this->addSql('ALTER TABLE teacher_attendance DROP FOREIGN KEY FK_E7FCAF31680CAB68');
        $this->addSql('ALTER TABLE teacher_work_item DROP FOREIGN KEY FK_9CB3D83D3CD988BE');
        $this->addSql('ALTER TABLE teacher_work_item DROP FOREIGN KEY FK_9CB3D83D96CA1090');
        $this->addSql('ALTER TABLE teacher_work_item DROP FOREIGN KEY FK_9CB3D83D41807E1D');
        $this->addSql('ALTER TABLE teacher_work_item DROP FOREIGN KEY FK_9CB3D83DAE80F5DF');
        $this->addSql('ALTER TABLE teacher_work_item DROP FOREIGN KEY FK_9CB3D83D6D5E7770');
        $this->addSql('ALTER TABLE teacher_work_set DROP FOREIGN KEY FK_9D6CC1CB41807E1D');
        $this->addSql('ALTER TABLE teacher_work_set DROP FOREIGN KEY FK_9D6CC1CBAE80F5DF');
    }
}
