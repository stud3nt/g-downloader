<?php

namespace App\Enum;

use App\Enum\Base\Enum;

abstract class UserRole extends Enum
{
    const Admin = 'ROLE_ADMIN';
    const User = 'ROLE_USER';

    public static function getData()
    {
        return [
            self::Admin => 'label.admin',
            self::User => 'label.user'
        ];
    }
}
