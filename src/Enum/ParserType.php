<?php

namespace App\Enum;

use App\Enum\Base\Enum;

abstract class ParserType extends Enum
{
    const Boards4chan = 'boards_4chan';
    const Imagefap = 'imagefap';
    const Xhamster = 'xhamster';
    const Reddit = 'reddit';
    const HentaiFoundry = 'hentai_foundry';

    public static function getData()
    {
        return [
            self::Boards4chan => 'Boards 4chan',
            self::Imagefap => 'imagefap.com',
            self::Xhamster => 'xhamster.com',
            self::Reddit => 'Reddit',
            self::HentaiFoundry => 'Hentai-Foundry'
        ];
    }
}
