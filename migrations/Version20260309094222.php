<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260309094222 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE access_grant (external_id VARCHAR(255) NOT NULL, resource_type VARCHAR(50) NOT NULL, resource_id VARCHAR(255) NOT NULL, principal_type VARCHAR(50) NOT NULL, principal_id VARCHAR(255) NOT NULL, permission VARCHAR(50) NOT NULL, created_at DATETIME DEFAULT NULL, model_external_id VARCHAR(255) NOT NULL, INDEX IDX_20901A1FF177EE5C (model_external_id), PRIMARY KEY (external_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_group (user_external_id VARCHAR(255) NOT NULL, group_external_id VARCHAR(255) NOT NULL, INDEX IDX_8F02BF9D366E1E25 (user_external_id), INDEX IDX_8F02BF9DBACDC871 (group_external_id), PRIMARY KEY (user_external_id, group_external_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE access_grant ADD CONSTRAINT FK_20901A1FF177EE5C FOREIGN KEY (model_external_id) REFERENCES model (external_id)');
        $this->addSql('ALTER TABLE user_group ADD CONSTRAINT FK_8F02BF9D366E1E25 FOREIGN KEY (user_external_id) REFERENCES user (external_id)');
        $this->addSql('ALTER TABLE user_group ADD CONSTRAINT FK_8F02BF9DBACDC871 FOREIGN KEY (group_external_id) REFERENCES `group` (external_id)');
        $this->addSql('ALTER TABLE `group` CHANGE user_count member_count INT NOT NULL');
        $this->addSql('ALTER TABLE model ADD base_model_id VARCHAR(255) DEFAULT NULL, ADD description LONGTEXT DEFAULT NULL, ADD system_prompt LONGTEXT DEFAULT NULL, ADD is_active TINYINT NOT NULL, ADD created_at DATETIME DEFAULT NULL, ADD owner_id VARCHAR(255) DEFAULT NULL, DROP owned_by');
        $this->addSql('ALTER TABLE model ADD CONSTRAINT FK_D79572D97E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (external_id)');
        $this->addSql('CREATE INDEX IDX_D79572D97E3C61F9 ON model (owner_id)');
        $this->addSql('ALTER TABLE user ADD username VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE access_grant DROP FOREIGN KEY FK_20901A1FF177EE5C');
        $this->addSql('ALTER TABLE user_group DROP FOREIGN KEY FK_8F02BF9D366E1E25');
        $this->addSql('ALTER TABLE user_group DROP FOREIGN KEY FK_8F02BF9DBACDC871');
        $this->addSql('DROP TABLE access_grant');
        $this->addSql('DROP TABLE user_group');
        $this->addSql('ALTER TABLE `group` CHANGE member_count user_count INT NOT NULL');
        $this->addSql('ALTER TABLE model DROP FOREIGN KEY FK_D79572D97E3C61F9');
        $this->addSql('DROP INDEX IDX_D79572D97E3C61F9 ON model');
        $this->addSql('ALTER TABLE model ADD owned_by VARCHAR(255) DEFAULT NULL, DROP base_model_id, DROP description, DROP system_prompt, DROP is_active, DROP created_at, DROP owner_id');
        $this->addSql('ALTER TABLE user DROP username');
    }
}
