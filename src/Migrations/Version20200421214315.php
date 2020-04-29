<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200421214315 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE parsed_nodes_settings (
            id INT(10) UNSIGNED AUTO_INCREMENT NOT NULL, 
            node_id INT(10) UNSIGNED DEFAULT NULL, 
            prefix_type VARCHAR(16) DEFAULT NULL, 
            prefix VARCHAR(255) DEFAULT NULL, 
            sufix_type VARCHAR(16) DEFAULT NULL,
            sufix VARCHAR(255) DEFAULT NULL,
            folder_type VARCHAR(16) DEFAULT NULL, 
            folder VARCHAR(255) DEFAULT NULL, 
            max_size INT DEFAULT 0 NOT NULL, 
            size_unit VARCHAR(16) DEFAULT NULL,
            max_width INT DEFAULT 0 NOT NULL, 
            max_height INT DEFAULT 0 NOT NULL, 
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP, 
            updated_at DATETIME DEFAULT NULL, 
            UNIQUE INDEX UNIQ__pns_node_id (node_id), 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE parsed_nodes_settings ADD CONSTRAINT FK__pns_node_id FOREIGN KEY (node_id) REFERENCES parsed_nodes (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('DROP TABLE IF EXISTS parsed_nodes_data');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE parsed_nodes_settings');
    }
}
