<?php

namespace App\Enum;

use App\Enum\Base\Enum;

abstract class SettingsGroups extends Enum
{
    const Common = 'common';
    const Reddit = 'reddit';
    const Boards4chan = 'boards_4chan';
    const HentaiFoundry = 'hentai_foundry';
    const ImageFap = 'imagefap';

    const Imgur = 'imgur';

    public static function getData()
    {
        return [
            self::Common => 'label.settings.groups.common.name',
            self::Reddit => 'label.settings.groups.reddit.name',
            self::Boards4chan => 'label.settings.groups.boards_4chan.name',
            self::HentaiFoundry => 'label.settings.groups.hentai_foundry.name',
            self::ImageFap => 'label.settings.groups.imagefap.name',

            self::Imgur => 'label.settings.groups.imgur.name',
        ];
    }
}
