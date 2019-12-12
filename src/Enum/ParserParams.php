<?php

namespace App\Enum;

use App\Enum\Base\Enum;

abstract class ParserParams extends Enum
{
    const Parser = 'parser';
    const Page = 'page';
    const Action = 'action';
    const Level = 'level';
    const Url = 'url';

    public static function getData()
    {
        return [
            self::Parser => 'parser',
            self::Page => 'page',
            self::Action => 'action',
            self::Level => 'level',
            self::Url => 'url'
        ];
    }
}
