<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200905115503 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE categories CHANGE active active TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE parsed_files CHANGE type type ENUM(\'image\', \'video\')');
        $this->addSql('ALTER TABLE parsed_files RENAME INDEX idx_pf__duplicate_of_id TO IDX_2F6A6F992CC33300');
        $this->addSql('ALTER TABLE parsed_nodes DROP FOREIGN KEY FK__parsed_nodes__parent_node_id');
        $this->addSql('DROP INDEX IDX__parsed_nodes__url ON parsed_nodes');
        $this->addSql('ALTER TABLE parsed_nodes CHANGE rating rating INT UNSIGNED DEFAULT 0 NOT NULL, CHANGE saved saved TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE blocked blocked TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE favorited favorited TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE finished finished TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE parsed_nodes ADD CONSTRAINT FK_34622A3C3445EB91 FOREIGN KEY (parent_node_id) REFERENCES parsed_nodes (id)');
        $this->addSql('CREATE INDEX IDX__parsed_nodes__url ON parsed_nodes (url)');
        $this->addSql('ALTER TABLE parsed_nodes RENAME INDEX idx__parsed_nodes__parent_node_id TO IDX_34622A3C3445EB91');
        $this->addSql('ALTER TABLE parsed_nodes RENAME INDEX idx_parsed_nodes__category_id TO IDX_34622A3C12469DE2');
        $this->addSql('ALTER TABLE parsed_nodes_tags RENAME INDEX idx_pnt__parsed_node_id TO IDX_69BE125926265C9F');
        $this->addSql('ALTER TABLE parsed_nodes_tags RENAME INDEX idx_png__tag_id TO IDX_69BE1259BAD26311');
        $this->addSql('ALTER TABLE parsed_nodes_settings DROP FOREIGN KEY FK__pns_node_id');
        $this->addSql('ALTER TABLE parsed_nodes_settings ADD CONSTRAINT FK_E8F4B08A460D9FD7 FOREIGN KEY (node_id) REFERENCES parsed_nodes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE parsed_nodes_settings RENAME INDEX uniq__pns_node_id TO UNIQ_E8F4B08A460D9FD7');
        $this->addSql('ALTER TABLE settings CHANGE level level INT DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD thumbnail VARCHAR(255) DEFAULT NULL, ADD info_exchange_file VARCHAR(32) DEFAULT NULL, CHANGE api_token api_token VARCHAR(32) DEFAULT NULL, CHANGE file_token file_token VARCHAR(32) DEFAULT NULL');
        $this->addSql('ALTER TABLE users RENAME INDEX uniq_users__email TO UNIQ_1483A5E9E7927C74');
        $this->addSql('ALTER TABLE users RENAME INDEX uniq_users__username TO UNIQ_1483A5E9F85E0677');
        $this->addSql('ALTER TABLE users RENAME INDEX uniq_users__name TO UNIQ_1483A5E95E237E06');
        $this->addSql('ALTER TABLE users RENAME INDEX uniq_users__surname TO UNIQ_1483A5E9E7769B0F');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE categories CHANGE active active TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE parsed_files CHANGE type type VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE parsed_files RENAME INDEX idx_2f6a6f992cc33300 TO IDX_pf__duplicate_of_id');
        $this->addSql('ALTER TABLE parsed_nodes DROP FOREIGN KEY FK_34622A3C3445EB91');
        $this->addSql('DROP INDEX IDX__parsed_nodes__url ON parsed_nodes');
        $this->addSql('ALTER TABLE parsed_nodes CHANGE rating rating INT UNSIGNED DEFAULT 0, CHANGE saved saved TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE blocked blocked TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE favorited favorited TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE finished finished TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE parsed_nodes ADD CONSTRAINT FK__parsed_nodes__parent_node_id FOREIGN KEY (parent_node_id) REFERENCES parsed_nodes (id) ON UPDATE CASCADE ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX__parsed_nodes__url ON parsed_nodes (url(191))');
        $this->addSql('ALTER TABLE parsed_nodes RENAME INDEX idx_34622a3c12469de2 TO IDX_parsed_nodes__category_id');
        $this->addSql('ALTER TABLE parsed_nodes RENAME INDEX idx_34622a3c3445eb91 TO IDX__parsed_nodes__parent_node_id');
        $this->addSql('ALTER TABLE parsed_nodes_settings DROP FOREIGN KEY FK_E8F4B08A460D9FD7');
        $this->addSql('ALTER TABLE parsed_nodes_settings ADD CONSTRAINT FK__pns_node_id FOREIGN KEY (node_id) REFERENCES parsed_nodes (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE parsed_nodes_settings RENAME INDEX uniq_e8f4b08a460d9fd7 TO UNIQ__pns_node_id');
        $this->addSql('ALTER TABLE parsed_nodes_tags RENAME INDEX idx_69be125926265c9f TO IDX_pnt__parsed_node_id');
        $this->addSql('ALTER TABLE parsed_nodes_tags RENAME INDEX idx_69be1259bad26311 TO IDX_png__tag_id');
        $this->addSql('ALTER TABLE settings CHANGE level level SMALLINT UNSIGNED DEFAULT 0');
        $this->addSql('ALTER TABLE users DROP thumbnail, DROP info_exchange_file, CHANGE file_token file_token VARCHAR(32) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE api_token api_token VARCHAR(32) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE users RENAME INDEX uniq_1483a5e95e237e06 TO UNIQ_users__name');
        $this->addSql('ALTER TABLE users RENAME INDEX uniq_1483a5e9f85e0677 TO UNIQ_users__username');
        $this->addSql('ALTER TABLE users RENAME INDEX uniq_1483a5e9e7927c74 TO UNIQ_users__email');
        $this->addSql('ALTER TABLE users RENAME INDEX uniq_1483a5e9e7769b0f TO UNIQ_users__surname');
    }
}
