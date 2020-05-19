<?php

namespace App\Utils;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AppHelper
{
    public static function getCurrentUser(TokenStorageInterface $tokenStorage): ?User
    {
        return $tokenStorage->getToken() && is_object($user = $tokenStorage->getToken()->getUser()) ? $user : null;
    }

    public static function getCurrentDate() : \DateTime
    {
        return new \DateTime('now');
    }

    public static function getRootDir()
    {
        self::checkPublicDir();

        return PUBLIC_DIR.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;
    }

    public static function getAngularSourceDir()
    {
        return self::getRootDir().'angular-source'.DIRECTORY_SEPARATOR;
    }

    public static function getPublicDir(): string
    {
        self::checkPublicDir();

        return PUBLIC_DIR.DIRECTORY_SEPARATOR;
    }

    public static function getDataDir()
    {
        self::checkPublicDir();

        return PUBLIC_DIR.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR;
    }

    public static function checkPublicDir(): void
    {
        if (!defined('PUBLIC_DIR')) {
            $ds = DIRECTORY_SEPARATOR;
            define('PUBLIC_DIR', __DIR__.$ds.'..'.$ds.'..'.$ds.'public');
        }
    }
}