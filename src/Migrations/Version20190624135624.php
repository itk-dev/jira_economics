<?php

declare(strict_types=1);

/*
 * This file is part of aakb/kontrolgruppen.
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
final class Version20190624135624 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE gs_order (id INT AUTO_INCREMENT NOT NULL, job_title VARCHAR(255) NOT NULL, order_lines LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', description LONGTEXT DEFAULT NULL, files LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', debitor INT DEFAULT NULL, marketing_account VARCHAR(255) DEFAULT NULL, department VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, postalcode INT DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, date DATE DEFAULT NULL, delivery_description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE gs_order');
    }
}
