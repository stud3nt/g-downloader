<?php

namespace App\Enum;

use App\Enum\Base\Enum;

abstract class CacheType extends Enum
{
    const PageLoaderStatus = 'page_loader_status';

    public static function getData()
    {
        return [
            self::PageLoaderStatus => 'Page loader status'
        ];
    }
}
