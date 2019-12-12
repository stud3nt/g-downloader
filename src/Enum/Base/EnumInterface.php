<?php

namespace App\Enum\Base;

interface EnumInterface
{
    public static function getKeys();
    public static function getValues();
    public static function getChoices();
    public static function getData();
    public static function get($key);
}