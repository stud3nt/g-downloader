<?php

namespace App\Enum;

use App\Enum\Base\Enum;

abstract class FileStatus extends Enum
{
    const New = 'new';
    const Queued = 'queued';
    const Downloaded = 'downloaded';
    const Downloading = 'downloading';

    public static function getData()
    {
        return [
            self::New => 'New',
            self::Queued => 'Queued',
            self::Downloaded => 'Downloaded',
            self::Downloading => 'Downloading'
        ];
    }
}
