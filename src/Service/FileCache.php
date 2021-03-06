<?php

namespace App\Service;

use App\Entity\User;
use App\Enum\DownloaderStatus;
use App\Utils\AppHelper;
use Symfony\Component\Filesystem\Filesystem;

class FileCache
{
    /** @var Filesystem */
    protected $fs;

    protected $cacheDirectory;

    protected $expirationDataFile;

    protected $expirationData = [];

    public function __construct(User $user = null)
    {
        $this->fs = new Filesystem();

        $userToken = $user->getFileToken();

        $this->cacheDirectory = AppHelper::getDataDir().'user-cache'.DIRECTORY_SEPARATOR.$userToken;

        if (!file_exists($this->cacheDirectory)) {
            $this->fs->mkdir($this->cacheDirectory);
        }

        $this->expirationDataFile = $this->cacheDirectory.DIRECTORY_SEPARATOR.'ex-data.json';

        if (!file_exists($this->expirationDataFile)) {
            file_put_contents($this->expirationDataFile, json_encode([]));
        }

        $this->expirationData = json_decode(file_get_contents($this->expirationDataFile), true);

        return $this;
    }

    /**
     * Save value in cache file
     *
     * @deprecated - removed since version 1.0
     * @param string $key - cache key
     * @param $value - stored value
     * @param int $expirationTime - cache expiration time (0 - never expires)
     * @return FileCache
     */
    public function save(string $key, $value, int $expirationTime = 0) : FileCache
    {
        file_put_contents($this->cacheFilePath($key), json_encode($value));

        $this->updateExpiration($key, $expirationTime);

        return $this;
    }

    /**
     * Proxy for save() function
     *
     * @param string $key - cache key
     * @param $value - stored value
     * @param int $expirationTime - cache expiration time (0 - never expires)
     *
     * @return FileCache
     */
    public function set(string $key, $value, int $expirationTime = 0) : FileCache
    {
        file_put_contents($this->cacheFilePath($key), json_encode($value));

        $this->updateExpiration($key, $expirationTime);

        return $this;
    }

    /**
     * Checks if data for specified key exists;
     *
     * @param string $key
     * @return bool
     * @throws \Exception
     */
    public function has(string $key) : bool
    {
        $cacheFilePath = $this->cacheFilePath($key);

        if ($this->fs->exists($cacheFilePath)) {
            $expirationTimestamp = $this->getExpirationTimestamp($key);

            if ($expirationTimestamp === 0 || (new \DateTime())->setTimestamp($expirationTimestamp) >= (new \DateTime())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $key
     * @param null $defaultValue
     * @return mixed|null
     * @throws \Exception
     */
    public function get(string $key, $defaultValue = null)
    {
        $cacheFilePath = $this->cacheFilePath($key);

        if ($this->fs->exists($cacheFilePath)) {
            $data = json_decode(file_get_contents($cacheFilePath), true);
            $expirationTimestamp = $this->getExpirationTimestamp($key);

            if ($expirationTimestamp === 0 || (new \DateTime())->setTimestamp($expirationTimestamp) >= (new \DateTime())) {
                return $data ? $data : $defaultValue;
            } else {
                $this->remove($key);
            }
        }

        return $defaultValue;
    }

    /**
     * Removes cache element by key
     *
     * @deprecated - removed since version 1.0
     * @param string $key
     */
    public function clear(string $key): void
    {
        $this->remove($key);
    }

    /**
     * Proxy for clear() function
     *
     * @param string $key
     */
    public function remove(string $key): void
    {
        $cacheFilePath = $this->cacheFilePath($key);

        if ($this->fs->exists($cacheFilePath)) {
            $this->fs->remove($cacheFilePath);
        }

        $this->updateExpiration($key, -1);
    }

    public function removeAll(): void
    {
        if ($this->expirationData) {
            foreach ($this->expirationData as $key => $timestamp) {
                $this->remove($key);
            }
        }

        $this->expirationData = [];
    }

    /**
     * Read cache entry by key
     *
     * @deprecated - removed since version 1.0
     * @param string $key
     * @return mixed|null
     * @throws \Exception
     */
    public function read(string $key)
    {
        return $this->get($key);
    }

    /**
     * Another proxy for read() function
     *
     * @deprecated
     * @param string $key
     * @return mixed|null
     * @throws \Exception
     */
    public function readSessionData(string $keyString)
    {
        return $this->get($keyString);
    }

    /**
     * Another proxy for save() function
     *
     * @param string $key - cache key
     * @param $value - stored value
     * @param int $expirationTime - cache expiration time (0 - never expires)
     * @return FileCache
     */
    public function saveSessionData(string $keyString, $value): void
    {
        $this->set($keyString, $value);
    }

    /**
     * Automatic save page loader description
     *
     * @param string $description
     * @throws \Exception
     */
    public function savePageLoaderDescription(string $description = ''): void
    {
        $pageLoaderData = $this->get('page_loader_status');

        if ($pageLoaderData) {
            $pageLoaderData['description'] = $description;
        } else {
            $pageLoaderData = [
                'progress' => 0,
                'description' => $description
            ];
        }

        $this->set('page_loader_status', $pageLoaderData);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getDownloaderData(): array
    {
        return [
            'downloaderStatus' => $this->get('downloader_status', DownloaderStatus::Idle)
        ];
    }

    protected function cacheFilePath(string $key): string
    {
        return $this->cacheDirectory.DIRECTORY_SEPARATOR.'c-'.str_replace('/[^a-zA-Z0-9\_\.\-\=]/', '_', $key).'.json';
    }

    protected function getExpirationTimestamp(string $key): int
    {
        return $this->expirationData[$key] ?? 0;
    }

    protected function updateExpiration(string $key, int $expirationTime = 0): void
    {
        if ($expirationTime > 0) {
            $expirationTime = (new \DateTime())->modify('+' . $expirationTime . ' seconds');
            $this->expirationData[$key] = $expirationTime->getTimestamp();
        } elseif ($expirationTime === 0) {
            $this->expirationData[$key] = 0;
        } else {
            if (array_key_exists($key, $this->expirationData)) {
                unset($this->expirationData[$key]);
            }
        }

        file_put_contents($this->expirationDataFile, json_encode($this->expirationData));
    }
}