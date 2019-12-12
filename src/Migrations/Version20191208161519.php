<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191208161519 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE parsed_nodes ADD COLUMN queued TINYINT(1) UNSIGNED DEFAULT 0 AFTER comments_no');
        $this->addSql('ALTER TABLE parsed_nodes ADD COLUMN blocked TINYINT(1) UNSIGNED DEFAULT 0 AFTER queued');
        $this->addSql('ALTER TABLE parsed_nodes ADD COLUMN favorited TINYINT(1) UNSIGNED DEFAULT 0 AFTER blocked');
        $this->addSql('ALTER TABLE parsed_nodes ADD COLUMN finished TINYINT(1) UNSIGNED DEFAULT 0 AFTER favorited');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE parsed_nodes DROP COLUMN queued');
        $this->addSql('ALTER TABLE parsed_nodes DROP COLUMN blocked');
        $this->addSql('ALTER TABLE parsed_nodes DROP COLUMN favorited');
        $this->addSql('ALTER TABLE parsed_nodes DROP COLUMN finished');
    }
}
