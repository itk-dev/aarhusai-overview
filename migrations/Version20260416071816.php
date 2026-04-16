<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260416071816 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE model (external_id VARCHAR(255) NOT NULL, site VARCHAR(50) NOT NULL, name VARCHAR(255) NOT NULL, base_model_id VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, system_prompt LONGTEXT DEFAULT NULL, is_active TINYINT NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (external_id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE model');
    }
}
