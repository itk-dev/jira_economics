<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190813093019 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE invoice ADD description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE invoice_entry ADD price NUMERIC(10, 2) NOT NULL, ADD amount NUMERIC(10, 2) NOT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE product product VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE jira_issue CHANGE project_id project_id INT DEFAULT NULL, CHANGE finished finished DATETIME DEFAULT NULL, CHANGE time_spent time_spent INT DEFAULT NULL, CHANGE invoiceEntryId invoiceEntryId INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE invoice DROP description');
        $this->addSql('ALTER TABLE invoice_entry DROP price, DROP amount, CHANGE description description VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE product product VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE jira_issue CHANGE project_id project_id INT DEFAULT NULL, CHANGE finished finished DATETIME DEFAULT \'NULL\', CHANGE time_spent time_spent INT DEFAULT NULL, CHANGE invoiceEntryId invoiceEntryId INT DEFAULT NULL');
    }
}
