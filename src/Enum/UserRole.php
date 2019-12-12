<?php

namespace App\Enum;

use App\Enum\Base\Enum;

abstract class UserRole extends Enum
{
    const Admin = 'admin';

    public static function getData()
    {
        return [
            self::Admin => 'label.admin',
        ];
    }
}
