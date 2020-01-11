<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Enum\PaginationMode;
use App\Enum\ParserType;
use App\Enum\SettingsGroup;
use App\Enum\SettingsType;
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
            'group_name' => SettingsGroup::Parser,
            'label' => 'label.settings.levels.start_level',
            'type' => SettingsType::Reddit,
        ],
        [
            'name' => 'INITIAL_PAGINATION',
            'value' => 'none',
            'group_name' => SettingsGroup::Parser,
            'label' => 'label.settings.levels.initial_pagination',
            'type' => SettingsType::Reddit,
        ],
        [
            'name' => 'INITIAL_LEVEL',
            'value' => 'boards_list',
            'group_name' => SettingsGroup::Parser,
            'label' => 'label.settings.levels.start_level',
            'type' => SettingsType::Boards4chan
        ],
        [
            'name' => 'INITIAL_PAGINATION',
            'value' => 'none',
            'group_name' => SettingsGroup::Parser,
            'label' => 'label.settings.levels.initial_pagination',
            'type' => SettingsType::Boards4chan
        ],
        [
            'name' => 'INITIAL_LEVEL',
            'value' => 'board',
            'group_name' => SettingsGroup::Parser,
            'label' => 'label.settings.levels.start_level',
            'type' => SettingsType::HentaiFoundry
        ],
        [
            'name' => 'INITIAL_PAGINATION',
            'value' => PaginationMode::Letters,
            'group_name' => SettingsGroup::Parser,
            'label' => 'label.settings.levels.initial_pagination',
            'type' => SettingsType::HentaiFoundry
        ],
        [
            'name' => 'INITIAL_LEVEL',
            'value' => 'owner',
            'group_name' => SettingsGroup::Parser,
            'label' => 'label.settings.levels.start_level',
            'type' => SettingsType::ImageFap
        ],
        [
            'name' => 'INITIAL_PAGINATION',
            'value' => PaginationMode::Numbers,
            'group_name' => SettingsGroup::Parser,
            'label' => 'label.settings.levels.initial_pagination',
            'type' => SettingsType::ImageFap
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
                    'label' => ':label',
                    'group_name' => ':group_name',
                    'type' => ':type'
                ])
                ->setParameters($setting)
                ->execute();
        }
    }

    public function down(Schema $schema) : void
    {

    }
}
