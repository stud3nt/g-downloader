<?php

namespace App\Enum;

use App\Enum\Base\Enum;

abstract class PaginationMode extends Enum
{
    const Numbers = 'numbers';
    const Letters = 'letters';
    const LoadMore = 'load_more';

    public static function getData()
    {
        return [
            self::Numbers => 'numbers',
            self::Letters => 'letters',
            self::LoadMore => 'load_more'
        ];
    }
}
