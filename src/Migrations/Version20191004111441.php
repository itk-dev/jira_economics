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
final class Version20191004111441 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE debtor (id INT AUTO_INCREMENT NOT NULL, number INT NOT NULL, label VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE debtor_user (debtor_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_CB4B6CA3B043EC6B (debtor_id), INDEX IDX_CB4B6CA3A76ED395 (user_id), PRIMARY KEY(debtor_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE debtor_user ADD CONSTRAINT FK_CB4B6CA3B043EC6B FOREIGN KEY (debtor_id) REFERENCES debtor (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE debtor_user ADD CONSTRAINT FK_CB4B6CA3A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE fos_user DROP account');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE debtor_user DROP FOREIGN KEY FK_CB4B6CA3B043EC6B');
        $this->addSql('DROP TABLE debtor');
        $this->addSql('DROP TABLE debtor_user');
        $this->addSql('ALTER TABLE fos_user ADD account VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
    }
}
