<?php

namespace App\Enum;

use App\Enum\Base\Enum;

abstract class NodeLevel extends Enum
{
    const File = 'file';
    const Gallery = 'gallery';
    const Board = 'board';
    const BoardsList = 'boards_list';
    const Owner = 'owner';

    public static function getLevelValue()
    {
        return [
            self::File => 0,
            self::Gallery => 1,
            self::Board => 2,
            self::BoardsList => 3,
            self::Owner => 4
        ];
    }
}
