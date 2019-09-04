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
final class Version20190904121437 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE invoice ADD customer_account_id INT NOT NULL, ADD recorded_date DATETIME DEFAULT NULL, ADD exported_date DATETIME DEFAULT NULL, ADD locked_customer_key VARCHAR(255) DEFAULT NULL, ADD locked_contact_name VARCHAR(255) DEFAULT NULL, ADD locked_type VARCHAR(255) DEFAULT NULL, ADD locked_account_key VARCHAR(255) DEFAULT NULL, ADD locked_sales_channel VARCHAR(255) DEFAULT NULL, DROP account_id');
        $this->addSql('ALTER TABLE invoice_entry ADD material_number VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE invoice ADD account_id INT DEFAULT NULL, DROP customer_account_id, DROP recorded_date, DROP exported_date, DROP locked_customer_key, DROP locked_contact_name, DROP locked_type, DROP locked_account_key, DROP locked_sales_channel');
        $this->addSql('ALTER TABLE invoice_entry DROP material_number');
    }
}
