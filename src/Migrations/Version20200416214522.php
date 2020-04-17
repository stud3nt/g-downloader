<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200416214522 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE parsed_files 
            DROP color_hash,
            ADD duplicate_of_id INT(10) UNSIGNED DEFAULT NULL, 
            ADD bin_hash VARCHAR(64) DEFAULT NULL, 
            ADD hex_hash VARCHAR(255) DEFAULT NULL
        ');
        $this->addSql('ALTER TABLE parsed_files ADD CONSTRAINT FK_pf__duplicate_id_parsed_files_id FOREIGN KEY (duplicate_of_id) REFERENCES parsed_files (id)');
        $this->addSql('CREATE INDEX IDX_pf__duplicate_of_id ON parsed_files (duplicate_of_id)');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE parsed_files DROP FOREIGN KEY FK_pf__duplicate_id_parsed_files_id');
        $this->addSql('DROP INDEX IDX_pf__duplicate_of_id ON parsed_files');
        $this->addSql('ALTER TABLE parsed_files DROP duplicate_of_id, DROP bin_hash, DROP hex_hash, ADD color_hash VARCHAR(64) AFTER description');
    }
}
