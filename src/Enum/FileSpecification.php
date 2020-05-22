<?php

namespace App\Enum;

use App\Enum\Base\Enum;

abstract class FileSpecification extends Enum
{
    const jpg = [
        'mimeType' => 'image/jpeg',
        'extension' => 'jpg',
        'type' => FileType::Image
    ];

    const pjpg = [
        'mimeType' => 'image/pjpeg',
        'extension' => 'jpg',
        'type' => FileType::Image
    ];

    const png = [
        'mimeType' => 'image/png',
        'extension' => 'png',
        'type' => FileType::Image
    ];

    const bmp = [
        'mimeType' => 'image/bmp',
        'extension' => 'bmp',
        'type' => FileType::Image
    ];

    const gif = [
        'mimeType' => 'image/gif',
        'extension' => 'gif',
        'type' => FileType::Image
    ];

    const webm = [
        'mimeType' => 'video/webm',
        'extension' => 'webm',
        'type' => FileType::Video
    ];

    const mp4 = [
        'mimeType' => 'video/mp4',
        'extension' => 'mp4',
        'type' => FileType::Video
    ];

    const wmv = [
        'mimeType' => 'video/x-ms-wmv',
        'extension' => 'wmv',
        'type' => FileType::Video
    ];

    const icon = [
        'mimeType' => 'image/x-icon',
        'extension' => 'ico',
        'type' => FileType::Image
    ];

    public static function getData()
    {
        return [
            'jpg' => self::jpg,
            'pjpg' => self::pjpg,
            'png' => self::png,
            'bmp' => self::bmp,
            'gif' => self::gif,
            'webm' => self::webm,
            'mp4' => self::mp4,
            'wmv' => self::wmv,
        ];
    }
}
