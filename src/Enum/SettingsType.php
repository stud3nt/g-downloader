<?php

namespace App\Enum;

use App\Enum\Base\Enum;

abstract class SettingsType extends Enum
{
    const Reddit = 'reddit';
    const Boards4chan = 'boards_4chan';
    const HentaiFoundry = 'hentai_foundry';
    const ImageFap = 'imagefap';
    const Imgur = 'imgur';
}
