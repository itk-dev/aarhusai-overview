<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260306143859 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `group` (external_id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, user_count INT NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (external_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE model (external_id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, owned_by VARCHAR(255) DEFAULT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (external_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (external_id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, role VARCHAR(50) NOT NULL, last_active_at DATETIME DEFAULT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (external_id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE `group`');
        $this->addSql('DROP TABLE model');
        $this->addSql('DROP TABLE user');
    }
}
