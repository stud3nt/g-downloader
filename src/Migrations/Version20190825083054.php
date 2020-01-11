<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Enum\SettingsGroup;
use App\Enum\SettingsLevel;
use App\Enum\SettingsType;
use App\Utils\AppHelper;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190825083054 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Basic settings data';
    }

    public function up(Schema $schema) : void
    {
        foreach ($this->getSettings() as $setting) {
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
        $this->connection->createQueryBuilder()
            ->delete('settings')
            ->execute();
    }

    protected function getSettings(): array
    {
        return [
            [
                'name' => 'DOWNLOAD_DIRECTORY',
                'value' => AppHelper::getPublicDir().'downloads',
                'label' => 'label.settings.download_catalog',
                'group_name' => SettingsGroup::Common,
                'type' => null,
                'level' => SettingsLevel::Download
            ],
            [
                'name' => 'APP_ID',
                'value' => 'wqZk8wMDLM2pyw',
                'label' => 'label.settings.groups.reddit.api_key',
                'group_name' => SettingsGroup::Parser,
                'type' => SettingsType::Reddit,
                'level' => SettingsLevel::Private
            ],
            [
                'name' => 'API_KEY',
                'value' => 'wqZk8wMDLM2pyw',
                'label' => 'label.settings.groups.reddit.api_key',
                'group_name' => SettingsGroup::Parser,
                'type' => SettingsType::Reddit,
                'level' => SettingsLevel::Private
            ],
            [
                'name' => 'APP_SECRET',
                'value' => 'b4CRnQ9vtg8UWmmvf9ue0Dc5EsI',
                'label' => 'label.settings.groups.reddit.app_secret',
                'group_name' => SettingsGroup::Parser,
                'type' => SettingsType::Reddit,
                'level' => SettingsLevel::Private
            ],
            [
                'name' => 'USERNAME',
                'value' => 'jagoslau',
                'label' => 'label.settings.groups.reddit.username',
                'group_name' => SettingsGroup::Parser,
                'type' => SettingsType::Reddit,
                'level' => SettingsLevel::Private
            ],
            [
                'name' => 'PASSWORD',
                'value' => '4710bbb',
                'label' => 'label.settings.groups.reddit.password',
                'group_name' => SettingsGroup::Parser,
                'type' => SettingsType::Reddit,
                'level' => SettingsLevel::Private
            ],
            [
                'name' => 'USER_AGENT',
                'value' => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:59.0) Gecko/20100101 Firefox/59.0',
                'label' => 'label.settings.groups.reddit.user_agent',
                'group_name' => SettingsGroup::Parser,
                'type' => SettingsType::Reddit,
                'level' => SettingsLevel::Private
            ],
            [
                'name' => 'ENDPOINT',
                'value' => 'https://www.reddit.com',
                'label' => 'label.settings.groups.reddit.endpoint',
                'group_name' => SettingsGroup::Parser,
                'type' => SettingsType::Reddit,
                'level' => SettingsLevel::Private
            ]
        ];
    }
}
