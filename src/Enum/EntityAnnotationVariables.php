<?php

namespace App\Enum;

use App\Enum\Base\Enum;

abstract class EntityAnnotationVariables extends Enum
{
    const Convertable = 'convertable';
    const Writable = 'writable';
    const Readable = 'readable';
}
