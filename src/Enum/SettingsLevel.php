<?php

namespace App\Enum;

use App\Enum\Base\Enum;

abstract class SettingsLevel extends Enum
{
    const Public = 0;
    const Download = 1;
    const Private = 2;
}
