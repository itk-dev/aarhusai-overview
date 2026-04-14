<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260316140458 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE access_grant ADD site VARCHAR(50) NOT NULL DEFAULT 'test'");
        $this->addSql("ALTER TABLE `group` ADD site VARCHAR(50) NOT NULL DEFAULT 'test'");
        $this->addSql("ALTER TABLE model ADD site VARCHAR(50) NOT NULL DEFAULT 'test'");
        $this->addSql("ALTER TABLE user ADD site VARCHAR(50) NOT NULL DEFAULT 'test'");
        $this->addSql('ALTER TABLE access_grant ALTER site DROP DEFAULT');
        $this->addSql('ALTER TABLE `group` ALTER site DROP DEFAULT');
        $this->addSql('ALTER TABLE model ALTER site DROP DEFAULT');
        $this->addSql('ALTER TABLE user ALTER site DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE access_grant DROP site');
        $this->addSql('ALTER TABLE `group` DROP site');
        $this->addSql('ALTER TABLE model DROP site');
        $this->addSql('ALTER TABLE user DROP site');
    }
}
