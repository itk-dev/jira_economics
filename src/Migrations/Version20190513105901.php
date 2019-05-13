<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190513105901 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE project (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, jira_key VARCHAR(255) NOT NULL, jira_id INT NOT NULL, avatar_url VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invoice (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, name VARCHAR(255) NOT NULL, recorded TINYINT(1) NOT NULL, created DATETIME NOT NULL, INDEX IDX_90651744166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invoice_entry (id INT AUTO_INCREMENT NOT NULL, invoice_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_16FBCDC52989F1FD (invoice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE jira_issue (id INT AUTO_INCREMENT NOT NULL, project_id INT DEFAULT NULL, invoice_entry_id_id INT DEFAULT NULL, issue_id INT NOT NULL, summary VARCHAR(255) NOT NULL, created DATETIME NOT NULL, finished DATETIME DEFAULT NULL, jira_users LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', time_spent INT DEFAULT NULL, INDEX IDX_3F6C748D166D1F9C (project_id), INDEX IDX_3F6C748DEC109FC2 (invoice_entry_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE invoice_entry ADD CONSTRAINT FK_16FBCDC52989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id)');
        $this->addSql('ALTER TABLE jira_issue ADD CONSTRAINT FK_3F6C748D166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE jira_issue ADD CONSTRAINT FK_3F6C748DEC109FC2 FOREIGN KEY (invoice_entry_id_id) REFERENCES invoice_entry (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_90651744166D1F9C');
        $this->addSql('ALTER TABLE jira_issue DROP FOREIGN KEY FK_3F6C748D166D1F9C');
        $this->addSql('ALTER TABLE invoice_entry DROP FOREIGN KEY FK_16FBCDC52989F1FD');
        $this->addSql('ALTER TABLE jira_issue DROP FOREIGN KEY FK_3F6C748DEC109FC2');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE invoice');
        $this->addSql('DROP TABLE invoice_entry');
        $this->addSql('DROP TABLE jira_issue');
    }
}
