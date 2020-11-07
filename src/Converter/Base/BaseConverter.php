<?php

namespace App\Converter\Base;

class BaseConverter
{
    public function convertFromEntityValue($value)
    {
        return $value;
    }

    public function convertToEntityValue($value)
    {
        return $value;
    }
}