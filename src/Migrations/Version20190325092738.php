<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190325092738 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_2FB3D0EE4D9ADAB ON project');
        $this->addSql('ALTER TABLE invoice_entry ADD invoice_id INT NOT NULL');
        $this->addSql('ALTER TABLE invoice_entry ADD CONSTRAINT FK_16FBCDC52989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id)');
        $this->addSql('CREATE INDEX IDX_16FBCDC52989F1FD ON invoice_entry (invoice_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE invoice_entry DROP FOREIGN KEY FK_16FBCDC52989F1FD');
        $this->addSql('DROP INDEX IDX_16FBCDC52989F1FD ON invoice_entry');
        $this->addSql('ALTER TABLE invoice_entry DROP invoice_id');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2FB3D0EE4D9ADAB ON project (jira_id)');
    }
}
