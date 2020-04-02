<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200331212912 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Ratio/rating reorganization';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE parsed_nodes 
            CHANGE custom_description personal_description TEXT,
            CHANGE ratio rating MEDIUMINT(6) UNSIGNED DEFAULT 0            
        ');

        $this->addSql('ALTER TABLE parsed_files 
            ADD COLUMN dimension_ratio DECIMAL(5,2) DEFAULT 0
        ');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE parsed_nodes 
            CHANGE personal_description custom_description TEXT,
            CHANGE rating ratio MEDIUMINT(6) UNSIGNED DEFAULT 0            
        ');

        $this->addSql('ALTER TABLE parsed_files 
            DROP dimension_ratio
        ');
    }
}
