<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200324091205 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Description and rating in nodes';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql("ALTER TABLE parsed_nodes ADD personal_rating INT UNSIGNED DEFAULT 0 NOT NULL AFTER ratio");
        $this->addSql("ALTER TABLE parsed_nodes ADD custom_description LONGTEXT DEFAULT NULL AFTER description");
    }

    public function down(Schema $schema) : void
    {
        $this->addSql("ALTER TABLE parsed_nodes DROP personal_rating");
        $this->addSql("ALTER TABLE parsed_nodes DROP custom_description");
    }
}
