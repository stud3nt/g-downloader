<?php

namespace App\Utils;

use Symfony\Component\DependencyInjection\ContainerInterface;

class AppHelper
{
    public static function getCurrentDate() : \DateTime
    {
        return new \DateTime('now');
    }

    public static function getRootDir()
    {
        return PUBLIC_DIR.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;
    }

    public static function getAngularSourceDir()
    {
        return self::getRootDir().'angular-source'.DIRECTORY_SEPARATOR;
    }

    public static function getPublicDir()
    {
        return PUBLIC_DIR.DIRECTORY_SEPARATOR;
    }

    public static function getDataDir()
    {
        return PUBLIC_DIR.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR;
    }

    /**
     * @deprecated - temporary replacer for user entity;
     * @return string
     */
    public static function getUserToken()
    {
        return 'xb466AkZ8TaDBBWJuYdsysm';
    }
}