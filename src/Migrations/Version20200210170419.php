<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200210170419 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql("ALTER TABLE parsed_files ADD COLUMN description VARCHAR(2048) DEFAULT NULL AFTER size");
    }

    public function down(Schema $schema) : void
    {
        $this->addSql("ALTER TABLE parsed_files DROP COLUMN description");
    }
}
