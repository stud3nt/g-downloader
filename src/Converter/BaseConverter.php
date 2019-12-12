<?php

namespace App\Converter;

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