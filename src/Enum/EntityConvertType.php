<?php

namespace App\Enum;

use App\Enum\Base\Enum;

abstract class EntityConvertType extends Enum
{
    const StdClass = 'std_class';
    const Array = 'array';
    const JsonArray = 'json_array';
}
