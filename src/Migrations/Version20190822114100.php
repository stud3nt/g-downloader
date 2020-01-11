<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190822114100 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Initial database structure';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE download (
            id MEDIUMINT(8) UNSIGNED AUTO_INCREMENT NOT NULL, 
            starts_at DATETIME NOT NULL, 
            ends_at DATETIME NOT NULL, 
            downloaded_files MEDIUMINT(8) UNSIGNED DEFAULT NULL, 
            PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        $this->addSql('CREATE TABLE files (
            id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
            identifier VARCHAR(64) NOT NULL, 
            parser VARCHAR(16) NOT NULL, 
            name VARCHAR(255) NOT NULL, 
            type ENUM(\'image\', \'video\'), 
            extension VARCHAR(8) NOT NULL, 
            mime_type VARCHAR(16) NOT NULL, 
            url VARCHAR(2048) NOT NULL,
            file_url VARCHAR(2048) NOT NULL,
            thumbnail VARCHAR(1024) DEFAULT NULL, 
            width SMALLINT(4) UNSIGNED DEFAULT 0, 
            height SMALLINT(4) UNSIGNED DEFAULT 0, 
            length MEDIUMINT(6) UNSIGNED DEFAULT 0, 
            size INT UNSIGNED DEFAULT 0, 
            color_hash VARCHAR(64) DEFAULT NULL, 
            status VARCHAR(16) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            downloaded_at DATETIME DEFAULT NULL, 
            uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP, 
            updated_at DATETIME DEFAULT NULL, 
            PRIMARY KEY(id),
            INDEX identifier_idx (identifier)
        ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

        $this->addSql('CREATE TABLE parsed_nodes (
            id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
            identifier VARCHAR(64) NOT NULL, 
            name VARCHAR(255) DEFAULT NULL, 
            parser VARCHAR(20) DEFAULT NULL, 
            level VARCHAR(20) NOT NULL, 
            url VARCHAR(2048) DEFAULT NULL, 
            description VARCHAR(4096) DEFAULT NULL, 
            ratio MEDIUMINT(8) UNSIGNED DEFAULT 0,
            images_no MEDIUMINT(8) UNSIGNED DEFAULT 0,
            comments_no MEDIUMINT(8) UNSIGNED DEFAULT 0,
            thumbnails LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', 
            local_thumbnails LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', 
            last_viewed_at DATETIME DEFAULT CURRENT_TIMESTAMP, 
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP, 
            updated_at DATETIME DEFAULT NULL, 
            INDEX identifier_idx (identifier), 
            INDEX url_idx (url), 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        $this->addSql('
            CREATE TABLE parsed_nodes_data (
                id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
                identifier VARCHAR(255) NOT NULL, 
                level VARCHAR(20) NOT NULL, 
                parser VARCHAR(20) NOT NULL, 
                images_no MEDIUMINT(8) UNSIGNED DEFAULT 0,
                comments_no MEDIUMINT(8) UNSIGNED DEFAULT 0,
                INDEX identifier_idx (identifier), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        $this->addSql('CREATE TABLE settings (
            id MEDIUMINT(6) UNSIGNED AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) DEFAULT NULL, 
            group_name VARCHAR(32) NOT NULL, 
            type VARCHAR(32) DEFAULT NULL, 
            level SMALLINT(2) UNSIGNED DEFAULT 0,
            label VARCHAR(255) DEFAULT NULL,
            description VARCHAR(2048) DEFAULT NULL, 
            value LONGTEXT DEFAULT NULL, 
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP, 
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

        $this->addSql('CREATE TABLE users (
            id MEDIUMINT(8) UNSIGNED AUTO_INCREMENT NOT NULL, 
            email VARCHAR(255) NOT NULL,
            password VARCHAR(255) NOT NULL, 
            username VARCHAR(255) NOT NULL, 
            role VARCHAR(255) NOT NULL, 
            is_active TINYINT(1) NOT NULL, 
            created_at DATETIME NOT NULL, 
            UNIQUE INDEX UNIQ_users__email (email), 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
