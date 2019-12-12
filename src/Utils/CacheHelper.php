<?php

namespace App\Utils;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class CacheHelper
{
    /** @var FilesystemAdapter */
    public static $cache = null;

    public static function getCacheAdapter() : void
    {
        if (!self::$cache) {
            self::$cache = new FilesystemAdapter();
        }
    }

    public static function getUserCache(string $key)
    {
        self::getCacheAdapter();

        $item = self::$cache->getItem($key);

        if ($item->isHit()) {
            return $item->get();
        }

        return null;
    }

    public static function setUserCache(string $key, $value) : bool
    {
        self::getCacheAdapter();

        $item = self::$cache->getItem($key);
        $item->set($value);

        self::$cache->save($item);

        return true;
    }

    public static function resetPageLoaderData() : bool
    {
        return self::setPageLoaderData([
            'progress' => 0,
            'description' => null
        ]);
    }

    public static function setPageLoaderData(array $data = []) : bool
    {
        return self::setUserCache('app_page_progress_data', $data);
    }

    public static function getPageLoaderData() : array
    {
        return self::getUserCache('app_page_progress_data') ?? [];
    }

    public static function setPageLoaderProgress(int $progress = 0) : bool
    {
        $data = self::getPageLoaderData();
        $data['progress'] = $progress;

        return self::setPageLoaderData($data);
    }

    public static function setPageLoaderDescription(string $description = null) : bool
    {
        $data = self::getPageLoaderData();
        $data['description'] = $description;

        return self::setPageLoaderData($data);
    }
}