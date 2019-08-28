<?php

declare(strict_types=1);

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190826121520 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE worklog (id INT AUTO_INCREMENT NOT NULL, invoice_entry_id INT NOT NULL, worklog_id INT NOT NULL, is_billed TINYINT(1) DEFAULT NULL, INDEX IDX_524AFE2EA51E131A (invoice_entry_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE worklog ADD CONSTRAINT FK_524AFE2EA51E131A FOREIGN KEY (invoice_entry_id) REFERENCES invoice_entry (id)');
        $this->addSql('DROP TABLE jira_issue');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE jira_issue (id INT AUTO_INCREMENT NOT NULL, project_id INT DEFAULT NULL, issue_id INT NOT NULL, summary VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, created DATETIME NOT NULL, finished DATETIME DEFAULT NULL, jira_users LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:array)\', time_spent INT DEFAULT NULL, invoiceEntryId INT DEFAULT NULL, INDEX IDX_3F6C748D166D1F9C (project_id), INDEX IDX_3F6C748D684BD90 (invoiceEntryId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE jira_issue ADD CONSTRAINT FK_3F6C748D166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE jira_issue ADD CONSTRAINT FK_3F6C748D684BD90 FOREIGN KEY (invoiceEntryId) REFERENCES invoice_entry (id) ON DELETE SET NULL');
        $this->addSql('DROP TABLE worklog');
    }
}
