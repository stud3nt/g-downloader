<?php

namespace App\Entity\Base;

use App\Entity\User;

interface ConvertableInterface
{
    public static function getArrayKeys($projectionType = null, $reservation = null, User $user = null);
}