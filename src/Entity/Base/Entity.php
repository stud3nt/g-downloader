<?php

namespace App\Entity\Base;

use App\Entity\User;

abstract class Entity implements EntityInterface
{
    public function getClass()
    {
        $pos = strpos(get_called_class(), 'App');

        if ($pos > 0)
            return substr(get_called_class(), $pos);

        return get_called_class();
    }

    public function getClassName()
    {
        $arr = explode('\\', $this->getClass());

        return end($arr);
    }

    public function getEntity()
    {
        return ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $this->getClassName())), '_');
    }

    public static function getArrayKeys($projectionType = null, $reservation = null, User $user = null)
    {
        return [];
    }
}