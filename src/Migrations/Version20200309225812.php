<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200309225812 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('
            CREATE TABLE categories (
                id MEDIUMINT(8) UNSIGNED AUTO_INCREMENT NOT NULL, 
                name VARCHAR(100) NOT NULL, 
                label VARCHAR(100) DEFAULT NULL, 
                symbol VARCHAR(100) NOT NULL, 
                active TINYINT(1) DEFAULT \'0\' NOT NULL, 
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP, 
                description VARCHAR(2048) DEFAULT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE parsed_nodes_tags (
            parsed_node_id INT(10) UNSIGNED NOT NULL, 
            tag_id MEDIUMINT(8) UNSIGNED NOT NULL, 
            INDEX IDX_pnt__parsed_node_id (parsed_node_id), 
            INDEX IDX_png__tag_id (tag_id), 
            PRIMARY KEY(parsed_node_id, tag_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE tags (
            id MEDIUMINT(8) UNSIGNED AUTO_INCREMENT NOT NULL, 
            name VARCHAR(28) NOT NULL, 
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP, 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE parsed_nodes_tags ADD CONSTRAINT FK__pnt__parsed_node_id FOREIGN KEY (parsed_node_id) REFERENCES parsed_nodes (id)');
        $this->addSql('ALTER TABLE parsed_nodes_tags ADD CONSTRAINT FK__png__tag_id FOREIGN KEY (tag_id) REFERENCES tags (id)');

        $this->addSql('ALTER TABLE parsed_nodes ADD category_id MEDIUMINT(8) UNSIGNED DEFAULT NULL');

        $this->addSql('ALTER TABLE parsed_nodes ADD CONSTRAINT FK_parsed_nodes__category_id FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('CREATE INDEX IDX_parsed_nodes__category_id ON parsed_nodes (category_id)');


        $this->addSql('ALTER TABLE users 
            CHANGE username username VARCHAR(40) NOT NULL, 
            CHANGE name name VARCHAR(40) NOT NULL, 
            CHANGE surname surname VARCHAR(60) NOT NULL
        ');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_users__name ON users (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_users__surname ON users (surname)');
        $this->addSql('ALTER TABLE users DROP INDEX UNIQ_1483A5E9F85E0677, ADD UNIQUE UNIQ_users__username (username)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE parsed_nodes DROP FOREIGN KEY FK_parsed_nodes__category_id');
        $this->addSql('ALTER TABLE parsed_nodes_tags DROP FOREIGN KEY FK__pnt__parsed_node_id');
        $this->addSql('ALTER TABLE parsed_nodes_tags DROP FOREIGN KEY FK__png__tag_id');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE parsed_nodes_tags');
        $this->addSql('DROP TABLE tags');

        $this->addSql('ALTER TABLE parsed_nodes DROP FOREIGN KEY FK_parsed_nodes__category_id');
        $this->addSql('DROP INDEX IDX_parsed_nodes__category_id ON parsed_nodes');

        $this->addSql('ALTER TABLE parsed_nodes DROP category_id');

        $this->addSql('ALTER TABLE users 
            CHANGE username username VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, 
            CHANGE name name VARCHAR(40) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, 
            CHANGE surname surname VARCHAR(60) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`
        ');
        $this->addSql('ALTER TABLE users DROP INDEX UNIQ_users__username, ADD UNIQUE UNIQ_1483A5E9F85E0677 (username)');
    }
}
