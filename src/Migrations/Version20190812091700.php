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
final class Version20190812091700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE fos_user CHANGE salt salt VARCHAR(255) DEFAULT NULL, CHANGE last_login last_login DATETIME DEFAULT NULL, CHANGE confirmation_token confirmation_token VARCHAR(180) DEFAULT NULL, CHANGE password_requested_at password_requested_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE invoice CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE invoice_entry CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE product product VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE jira_issue CHANGE project_id project_id INT DEFAULT NULL, CHANGE finished finished DATETIME DEFAULT NULL, CHANGE time_spent time_spent INT DEFAULT NULL, CHANGE invoiceEntryId invoiceEntryId INT DEFAULT NULL');
        $this->addSql('ALTER TABLE gs_order CHANGE order_lines order_lines LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', CHANGE files files LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', CHANGE debitor debitor INT DEFAULT NULL, CHANGE marketing_account marketing_account VARCHAR(255) DEFAULT NULL, CHANGE department department VARCHAR(255) DEFAULT NULL, CHANGE address address VARCHAR(255) DEFAULT NULL, CHANGE postalcode postalcode INT DEFAULT NULL, CHANGE city city VARCHAR(255) DEFAULT NULL, CHANGE date date DATE DEFAULT NULL, CHANGE own_cloud_shared_files own_cloud_shared_files LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', CHANGE order_status order_status VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE fos_user CHANGE salt salt VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE last_login last_login DATETIME DEFAULT \'NULL\', CHANGE confirmation_token confirmation_token VARCHAR(180) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE password_requested_at password_requested_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE gs_order CHANGE order_lines order_lines LONGTEXT DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:array)\', CHANGE files files LONGTEXT DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:array)\', CHANGE debitor debitor INT DEFAULT NULL, CHANGE marketing_account marketing_account VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE department department VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE address address VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE postalcode postalcode INT DEFAULT NULL, CHANGE city city VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE date date DATE DEFAULT \'NULL\', CHANGE own_cloud_shared_files own_cloud_shared_files LONGTEXT DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:array)\', CHANGE order_status order_status VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE invoice CHANGE description description VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE invoice_entry CHANGE description description VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE product product VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE jira_issue CHANGE project_id project_id INT DEFAULT NULL, CHANGE finished finished DATETIME DEFAULT \'NULL\', CHANGE time_spent time_spent INT DEFAULT NULL, CHANGE invoiceEntryId invoiceEntryId INT DEFAULT NULL');
    }
}
