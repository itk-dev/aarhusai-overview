<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260602120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add access_count column on model';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE model ADD access_count INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE model DROP access_count');
    }
}
