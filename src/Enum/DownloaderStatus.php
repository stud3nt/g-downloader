<?php

namespace App\Enum;

use App\Enum\Base\Enum;

abstract class DownloaderStatus extends Enum
{
    const Idle = 'idle';
    const Downloading = 'downloading';
    const Ending = 'ending';
    const Continuation = 'continuation';
    const WaitingForResponse = 'waiting_for_response';

    public static function getData()
    {
        return [
            self::Idle => 'Idle',
            self::Downloading => 'Downloading',
            self::Continuation => 'Continuation',
            self::Ending => 'Ending',
            self::WaitingForResponse => 'Waiting for response'
        ];
    }
}
