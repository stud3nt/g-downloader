<?php

namespace App\Enum;

use App\Enum\Base\Enum;

abstract class SettingsLevels extends Enum
{
    const System = 1; // vulnerable system settings
    const Initial = 2; // initial (start) settings
    const Download = 3; // download settings

    public static function getData()
    {
        return [
            self::System => 'label.settings.levels.system',
            self::Initial => 'label.settings.levels.initial',
            self::Download => 'label.settings.levels.download'
        ];
    }
}
