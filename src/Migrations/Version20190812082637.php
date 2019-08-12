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
final class Version20190812082637 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE fos_user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', portal_apps LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', UNIQUE INDEX UNIQ_957A647992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_957A6479A0D96FBF (email_canonical), UNIQUE INDEX UNIQ_957A6479C05FB297 (confirmation_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE expense_category (id INT NOT NULL, name VARCHAR(255) NOT NULL, unit_price NUMERIC(16, 4) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE invoice_entry CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE product product VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE jira_issue CHANGE project_id project_id INT DEFAULT NULL, CHANGE finished finished DATETIME DEFAULT NULL, CHANGE time_spent time_spent INT DEFAULT NULL, CHANGE invoiceEntryId invoiceEntryId INT DEFAULT NULL');
        $this->addSql('ALTER TABLE gs_order ADD issue_id INT NOT NULL, ADD issue_key VARCHAR(255) NOT NULL, ADD own_cloud_shared_files LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD order_status VARCHAR(255) DEFAULT NULL, CHANGE order_lines order_lines LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', CHANGE files files LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', CHANGE debitor debitor INT DEFAULT NULL, CHANGE marketing_account marketing_account VARCHAR(255) DEFAULT NULL, CHANGE department department VARCHAR(255) DEFAULT NULL, CHANGE address address VARCHAR(255) DEFAULT NULL, CHANGE postalcode postalcode INT DEFAULT NULL, CHANGE city city VARCHAR(255) DEFAULT NULL, CHANGE date date DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE fos_user');
        $this->addSql('DROP TABLE expense_category');
        $this->addSql('ALTER TABLE gs_order DROP issue_id, DROP issue_key, DROP own_cloud_shared_files, DROP order_status, CHANGE order_lines order_lines LONGTEXT DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:array)\', CHANGE files files LONGTEXT DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:array)\', CHANGE debitor debitor INT DEFAULT NULL, CHANGE marketing_account marketing_account VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE department department VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE address address VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE postalcode postalcode INT DEFAULT NULL, CHANGE city city VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE date date DATE DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE invoice_entry CHANGE description description VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE product product VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE jira_issue CHANGE project_id project_id INT DEFAULT NULL, CHANGE finished finished DATETIME DEFAULT \'NULL\', CHANGE time_spent time_spent INT DEFAULT NULL, CHANGE invoiceEntryId invoiceEntryId INT DEFAULT NULL');
    }
}
