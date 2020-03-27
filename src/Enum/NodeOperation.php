<?php

namespace App\Enum;

use App\Enum\Base\Enum;

abstract class NodeOperation extends Enum
{
    const Reload = 'reload';
    const HardReload = 'hard_reload';
    const Pagination = 'pagination';
    const Update = 'update';

    public static function getData()
    {
        return [
            self::Reload => 'reload',
            self::HardReload => 'hard_reload',
            self::Update => 'update',
            self::Pagination => 'pagination'
        ];
    }
}
