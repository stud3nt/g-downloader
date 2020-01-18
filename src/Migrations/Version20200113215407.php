<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200113215407 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Rename file table and adding relations between nodes and files';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('RENAME TABLE files TO parsed_files');

        // parsed_nodes
        $this->addSql('ALTER TABLE parsed_nodes ADD COLUMN parent_node_id INT(10) UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE parsed_nodes CHANGE queued saved TINYINT(1) NOT NULL DEFAULT 0');

        $this->addSql('ALTER TABLE parsed_nodes DROP INDEX url_idx');
        $this->addSql('ALTER TABLE parsed_nodes DROP INDEX identifier_idx');
        $this->addSql('ALTER TABLE parsed_nodes ADD INDEX IDX__parsed_nodes__identifier (identifier)');
        $this->addSql('ALTER TABLE parsed_nodes ADD INDEX IDX__parsed_nodes__url (url)');
        $this->addSql('ALTER TABLE parsed_nodes ADD INDEX IDX__parsed_nodes__parent_node_id (parent_node_id)');

        $this->addSql('ALTER TABLE parsed_nodes ADD CONSTRAINT FK__parsed_nodes__parent_node_id FOREIGN KEY (parent_node_id) 
                    REFERENCES parsed_nodes(`id`) ON DELETE SET NULL ON UPDATE CASCADE');

        // parsed_files
        $this->addSql('ALTER TABLE parsed_files ADD COLUMN node_id INT(10) UNSIGNED DEFAULT NULL');

        $this->addSql('ALTER TABLE parsed_files DROP INDEX identifier_idx');
        $this->addSql('ALTER TABLE parsed_files ADD INDEX IDX__parsed_files__identifier (identifier)');
        $this->addSql('ALTER TABLE parsed_files ADD INDEX IDX__parsed_files__node_id (node_id)');

        $this->addSql('ALTER TABLE parsed_files ADD CONSTRAINT FK__parsed_files__parsed_node FOREIGN KEY (node_id) 
                    REFERENCES parsed_nodes(`id`) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // parsed_nodes
        $this->addSql('ALTER TABLE parsed_nodes DROP INDEX IDX__parsed_nodes__identifier');
        $this->addSql('ALTER TABLE parsed_nodes DROP INDEX IDX__parsed_nodes__url');
        $this->addSql('ALTER TABLE parsed_nodes DROP INDEX IDX__parsed_nodes__parent_node_id');
        $this->addSql('ALTER TABLE parsed_nodes ADD INDEX identifier_idx (identifier)');
        $this->addSql('ALTER TABLE parsed_nodes ADD INDEX url_idx (url)');

        // parsed_files
        $this->addSql('ALTER TABLE parsed_files DROP FOREIGN KEY FK__parsed_files__parsed_node');
        $this->addSql('ALTER TABLE parsed_files DROP INDEX IDX__parsed_files__identifier');
        $this->addSql('ALTER TABLE parsed_files DROP INDEX IDX__parsed_files__node_id');
        $this->addSql('ALTER TABLE parsed_files ADD INDEX identifier_idx (identifier)');
        $this->addSql('ALTER TABLE parsed_files DROP COLUMN node_id');

        $this->addSql('RENAME TABLE parsed_files TO files');
    }
}
