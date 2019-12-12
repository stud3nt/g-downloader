<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Enum\SettingsGroups;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190825083054 extends AbstractMigration
{
    protected $settings = [
        SettingsGroups::Reddit => [
            [
                'name' => 'APP_ID',
                'value' => 'UT44_uCaSogELA',
                'label' => 'label.settings.groups.reddit.api_key',
                'group_name' => SettingsGroups::Reddit
            ],
            [
                'name' => 'API_KEY',
                'value' => 'UT44_uCaSogELA',
                'label' => 'label.settings.groups.reddit.api_key',
                'group_name' => SettingsGroups::Reddit
            ],
            [
                'name' => 'APP_SECRET',
                'value' => 'JJYhJjSIzfIJAqX8zybFCfKzbR8',
                'label' => 'label.settings.groups.reddit.app_secret',
                'group_name' => SettingsGroups::Reddit
            ],
            [
                'name' => 'USERNAME',
                'value' => 'jagoslau',
                'label' => 'label.settings.groups.reddit.username',
                'group_name' => SettingsGroups::Reddit
            ],
            [
                'name' => 'PASSWORD',
                'value' => '4710bbb',
                'label' => 'label.settings.groups.reddit.password',
                'group_name' => SettingsGroups::Reddit
            ],
            [
                'name' => 'USER_AGENT',
                'value' => '4710bbb',
                'label' => 'label.settings.groups.reddit.user_agent',
                'group_name' => SettingsGroups::Reddit
            ],
            [
                'name' => 'ENDPOINT',
                'value' => '4710bbb',
                'label' => 'label.settings.groups.reddit.endpoint',
                'group_name' => SettingsGroups::Reddit
            ],
            [
                'name' => 'CLIENT_ID',
                'value' => '6799e10150d229b',
                'label' => 'label.settings.groups.imgur.client_id',
                'group_name' => SettingsGroups::Imgur
            ],
            [
                'name' => 'CLIENT_SECRET',
                'value' => '2205aa62ebe7bfb4e69d5846e3e12fae10138f90',
                'label' => 'label.settings.groups.imgur.client_secret',
                'group_name' => SettingsGroups::Imgur
            ],
        ]
    ];

    public function getDescription() : string
    {
        return 'Basic settings data';
    }

    public function up(Schema $schema) : void
    {
        foreach ($this->settings as $group => $settings) {
            foreach ($settings as $setting) {
                $this->connection->createQueryBuilder()
                    ->insert('settings')
                    ->values([
                        'name' => ':name',
                        'value' => ':value',
                        'label' => ':label',
                        'group_name' => ':group_name'
                    ])
                    ->setParameters($setting)
                    ->execute();
            }
        }
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
