<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260504121559 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename site "test" to "dev" in model table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE model SET site = 'dev' WHERE site = 'test'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE model SET site = 'test' WHERE site = 'dev'");
    }
}
