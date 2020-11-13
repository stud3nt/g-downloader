<?php

namespace App\Factory\Base;

use App\Converter\ObjectSerializer;

class BaseFactory
{
    protected ObjectSerializer $objectSerializer;

    public function __construct()
    {
        $this->objectSerializer = new ObjectSerializer();
    }
}