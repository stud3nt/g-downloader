<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Enum\PaginationMode;
use App\Enum\ParserType;
use App\Enum\SettingsLevels;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191001175238 extends AbstractMigration
{
    private $initialSettings = [
        [
            'name' => 'INITIAL_LEVEL',
            'value' => 'boards_list',
            'parser' => ParserType::Reddit,
            'label' => 'label.settings.levels.start_level',
            'level' => SettingsLevels::Initial,
        ],
        [
            'name' => 'INITIAL_PAGINATION',
            'value' => 'none',
            'parser' => ParserType::Reddit,
            'label' => 'label.settings.levels.initial_pagination',
            'level' => SettingsLevels::Initial,
        ],
        [
            'name' => 'INITIAL_LEVEL',
            'value' => 'boards_list',
            'parser' => ParserType::Boards4chan,
            'label' => 'label.settings.levels.start_level',
            'level' => SettingsLevels::Initial
        ],
        [
            'name' => 'INITIAL_PAGINATION',
            'value' => 'none',
            'parser' => ParserType::Boards4chan,
            'label' => 'label.settings.levels.initial_pagination',
            'level' => SettingsLevels::Initial
        ],
        [
            'name' => 'INITIAL_LEVEL',
            'value' => 'board',
            'parser' => ParserType::HentaiFoundry,
            'label' => 'label.settings.levels.start_level',
            'level' => SettingsLevels::Initial
        ],
        [
            'name' => 'INITIAL_PAGINATION',
            'value' => PaginationMode::Letters,
            'parser' => ParserType::HentaiFoundry,
            'label' => 'label.settings.levels.initial_pagination',
            'level' => SettingsLevels::Initial
        ],
        [
            'name' => 'INITIAL_LEVEL',
            'value' => 'owner',
            'parser' => ParserType::Imagefap,
            'label' => 'label.settings.levels.start_level',
            'level' => SettingsLevels::Initial
        ],
        [
            'name' => 'INITIAL_PAGINATION',
            'value' => PaginationMode::Numbers,
            'parser' => ParserType::Imagefap,
            'label' => 'label.settings.levels.initial_pagination',
            'level' => SettingsLevels::Initial
        ]
    ];

    public function getDescription() : string
    {
        return 'Adding settings level and inserting parsers initial settings';
    }

    public function up(Schema $schema) : void
    {
        foreach ($this->initialSettings as $parserName => $setting) {
            $this->connection->createQueryBuilder()
                ->insert('settings')
                ->values([
                    'name' => ':name',
                    'value' => ':value',
                    'level' => ':level',
                    'label' => ':label',
                    'group_name' => ':parser'
                ])
                ->setParameters($setting)
                ->execute();
        }
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE settings DROP COLUMN level');
    }
}
