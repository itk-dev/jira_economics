<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190531073247 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE invoice_entry ADD description VARCHAR(255) DEFAULT NULL, ADD account VARCHAR(255) NOT NULL, ADD product VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE jira_issue CHANGE project_id project_id INT DEFAULT NULL, CHANGE invoice_entry_id_id invoice_entry_id_id INT DEFAULT NULL, CHANGE finished finished DATETIME DEFAULT NULL, CHANGE time_spent time_spent INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE invoice_entry DROP description, DROP account, DROP product');
        $this->addSql('ALTER TABLE jira_issue CHANGE project_id project_id INT DEFAULT NULL, CHANGE invoice_entry_id_id invoice_entry_id_id INT DEFAULT NULL, CHANGE finished finished DATETIME DEFAULT \'NULL\', CHANGE time_spent time_spent INT DEFAULT NULL');
    }
}
