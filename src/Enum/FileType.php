<?php

namespace App\Enum;

use App\Enum\Base\Enum;

abstract class FileType extends Enum
{
    const Video = 'video';
    const Image = 'image';

    public static function getData()
    {
        return [
            self::Video => 'Movie',
            self::Image => 'Image'
        ];
    }
}
