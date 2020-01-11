<?php

namespace App\Enum;

use App\Enum\Base\Enum;

abstract class SettingsGroup extends Enum
{
    const Common = 'common';
    const System = 'system';
    const Parser = 'parser';
    const ImageServer = 'image_server';
}
