<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190607113040 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE customer (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, att VARCHAR(255) NOT NULL, cvr INT NOT NULL, ean VARCHAR(16) NOT NULL, debtor INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE invoice_entry CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE product product VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE jira_issue CHANGE project_id project_id INT DEFAULT NULL, CHANGE invoice_entry_id_id invoice_entry_id_id INT DEFAULT NULL, CHANGE finished finished DATETIME DEFAULT NULL, CHANGE time_spent time_spent INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE customer');
        $this->addSql('ALTER TABLE invoice_entry CHANGE description description VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE product product VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE jira_issue CHANGE project_id project_id INT DEFAULT NULL, CHANGE invoice_entry_id_id invoice_entry_id_id INT DEFAULT NULL, CHANGE finished finished DATETIME DEFAULT \'NULL\', CHANGE time_spent time_spent INT DEFAULT NULL');
    }
}
