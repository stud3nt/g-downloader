<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191222125002 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('
            ALTER TABLE files 
                DROP status, 
                CHANGE parser parser VARCHAR(20) DEFAULT NULL, 
                CHANGE name name VARCHAR(255) DEFAULT NULL, 
                CHANGE type type ENUM(\'image\', \'video\'), 
                CHANGE width width MEDIUMINT(4) UNSIGNED DEFAULT 0, 
                CHANGE height height MEDIUMINT(4) UNSIGNED DEFAULT 0, 
                CHANGE size size INT(11) UNSIGNED DEFAULT 0 NOT NULL
        ');
        $this->addSql('DROP INDEX url_idx ON parsed_nodes');
        $this->addSql('
            ALTER TABLE parsed_nodes 
                CHANGE url url VARCHAR(2048) NOT NULL, 
                CHANGE ratio ratio MEDIUMINT(6) UNSIGNED DEFAULT 0 NOT NULL, 
                CHANGE images_no images_no MEDIUMINT(6) UNSIGNED DEFAULT 0 NOT NULL, 
                CHANGE comments_no comments_no MEDIUMINT(6) UNSIGNED DEFAULT 0 NOT NULL, 
                CHANGE queued queued TINYINT(1) DEFAULT 0 NOT NULL, 
                CHANGE blocked blocked TINYINT(1) DEFAULT 0 NOT NULL, 
                CHANGE favorited favorited TINYINT(1) DEFAULT 0 NOT NULL, 
                CHANGE finished finished TINYINT(1) DEFAULT 0 NOT NULL
        ');
        $this->addSql('CREATE INDEX url_idx ON parsed_nodes (url)');
        $this->addSql('DROP INDEX identifier_idx ON parsed_nodes_data');
        $this->addSql('
            ALTER TABLE parsed_nodes_data 
                CHANGE identifier identifier VARCHAR(64) NOT NULL, 
                CHANGE images_no images_no MEDIUMINT(6) UNSIGNED DEFAULT 0 NOT NULL, 
                CHANGE comments_no comments_no MEDIUMINT(6) UNSIGNED DEFAULT 0 NOT NULL
        ');
        $this->addSql('CREATE INDEX identifier_idx ON parsed_nodes_data (identifier)');
        $this->addSql('
            ALTER TABLE users 
                ADD salt VARCHAR(64) NOT NULL AFTER token, 
                ADD last_logged_at DATETIME DEFAULT NULL AFTER created_at,
                ADD updated_at DATETIME DEFAULT NULL AFTER last_logged_at, 
                ADD name VARCHAR(40) DEFAULT NULL AFTER username,
                ADD surname VARCHAR(60) DEFAULT NULL after name,
                CHANGE role roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', 
                CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9F85E0677 ON users (username)');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('
            ALTER TABLE files ADD status VARCHAR(16) NOT NULL COLLATE utf8_unicode_ci, 
                CHANGE type type VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
                CHANGE width width SMALLINT UNSIGNED DEFAULT 0, 
                CHANGE height height SMALLINT UNSIGNED DEFAULT 0, 
                CHANGE size size INT UNSIGNED DEFAULT 0, 
                CHANGE name name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
                CHANGE parser parser VARCHAR(16) NOT NULL COLLATE utf8_unicode_ci
        ');
        $this->addSql('DROP INDEX url_idx ON parsed_nodes');
        $this->addSql('
            ALTER TABLE parsed_nodes 
                CHANGE ratio ratio INT UNSIGNED DEFAULT 0, 
                CHANGE images_no images_no INT UNSIGNED DEFAULT 0, 
                CHANGE comments_no comments_no INT UNSIGNED DEFAULT 0, 
                CHANGE queued queued TINYINT(1) DEFAULT \'0\', 
                CHANGE blocked blocked TINYINT(1) DEFAULT \'0\', 
                CHANGE favorited favorited TINYINT(1) DEFAULT \'0\', 
                CHANGE finished finished TINYINT(1) DEFAULT \'0\', 
                CHANGE url url VARCHAR(2048) DEFAULT NULL COLLATE utf8mb4_unicode_ci
        ');
        $this->addSql('CREATE INDEX url_idx ON parsed_nodes (url(191))');
        $this->addSql('DROP INDEX identifier_idx ON parsed_nodes_data');
        $this->addSql('
            ALTER TABLE parsed_nodes_data 
                CHANGE images_no images_no INT UNSIGNED DEFAULT 0, 
                CHANGE comments_no comments_no INT UNSIGNED DEFAULT 0, 
                CHANGE identifier identifier VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci
        ');
        $this->addSql('CREATE INDEX identifier_idx ON parsed_nodes_data (identifier(191))');
        $this->addSql('
            ALTER TABLE users 
                DROP salt, 
                DROP updated_at, 
                DROP name,
                DROP surname,
                DROP last_logged_at,
                CHANGE role role VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
                CHANGE created_at created_at DATETIME NOT NULL
        ');
    }
}
