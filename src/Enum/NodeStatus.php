<?php

namespace App\Enum;

use App\Enum\Base\Enum;

abstract class NodeStatus extends Enum
{
    const Waiting = 'waiting'; // waiting/in action
    const Blocked = 'blocked'; // banned/blocked;
    const Favorited = 'favorited'; // favorited (displayed on top of list);
    const Finished = 'finished'; // finished (all interesting images downloaded)
    const Queued = 'queued'; // added to download queue;
    const NewContent = 'new_content'; // new content available

    public static function getData()
    {
        return [
            self::Waiting => 'waiting',
            self::Blocked => 'blocked',
            self::Favorited => 'favorited',
            self::Finished => 'finished',
            self::Queued => 'queued',
            self::NewContent => 'new_content'
        ];
    }
}
