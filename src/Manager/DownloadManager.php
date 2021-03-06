<?php

namespace App\Manager;

use App\Converter\ModelConverter;
use App\Entity\Parser\File;
use App\Entity\User;
use App\Enum\DownloaderStatus;
use App\Factory\RedisFactory;
use App\Manager\Base\EntityManager;
use App\Manager\Object\FileManager;
use App\Model\Download\DownloadStatus;
use App\Model\ParsedFile;
use App\Repository\FileRepository;
use App\Utils\FilesHelper;
use Doctrine\ORM\NonUniqueResultException;
use ReflectionException;

class DownloadManager extends EntityManager
{
    protected $entityName = 'Parser\File';

    /** @var FileManager */
    protected $fileManager;

    /** @var FileRepository */
    protected $repository;

    /** @var ModelConverter */
    protected $modelConverter;

    /** @var \Predis\ClientInterface|\Redis|\RedisCluster */
    protected $redis;

    /** @required */
    public function init(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
        $this->modelConverter = new ModelConverter();
        $this->redis = (new RedisFactory())->initializeConnection();
    }

    /**
     * @param User $user
     * @param File[] $queuedFiles
     * @return array
     * @throws NonUniqueResultException
     * @throws ReflectionException
     */
    public function createStatusData(User $user, string $status = DownloaderStatus::Idle, array $queuedFiles = []): array
    {
        $redisKey = 'downloader_data_'.$user->getApiToken();
        $queuedData = $this->fileManager->getQueuedFilesData();
        $downloadedData = $this->fileManager->getDownloadedFilesData();

        // total files data
        $downloadStatus = (new DownloadStatus())
            ->setStatus($status)
            ->setQueuedFilesCount($queuedData->count ?? 0)
            ->setQueuedFilesSize($queuedData->size ?? 0)
            ->setQueuedFilesTextSize(
                FilesHelper::bytesToSize($queuedData->size ?? 0)
            )
            ->setDownloadedFilesCount($downloadedData->count ?? 0)
            ->setDownloadedFilesSize($queuedData->size ?? 0)
            ->setDownloadedFilesTextSize(
                FilesHelper::bytesToSize($downloadedData->size ?? 0)
            );

        if ($queuedFiles) {
            foreach ($queuedFiles as $queuedFile) {
                $parsedFile = new ParsedFile();
                $this->modelConverter->setData($queuedFile, $parsedFile, true);
                $downloadStatus->addQueuedFile($parsedFile);
            }
        }

        $data = $this->serializer->normalize($downloadStatus);

        $this->updateQueuedFilesStatuses($data);

        $this->redis->set($redisKey, json_encode($data));

        return $data;
    }

    /**
     * @param User $user
     * @param ParsedFile $parsedFile
     * @throws NonUniqueResultException
     * @throws ReflectionException
     */
    public function increaseQueueByParsedFile(User $user, ParsedFile $parsedFile)
    {
        $status = new DownloadStatus();

        $cachedData = $this->getStatusData($user);

        $this->modelConverter->setData($cachedData, $status, true);

        $status->increaseByParsedFile($parsedFile);

        $data = $this->modelConverter->convert($status);

        $this->setStatusData($user, $data);
    }

    /**
     * @param User $user
     * @param ParsedFile $parsedFile
     * @throws NonUniqueResultException
     * @throws ReflectionException
     */
    public function decreaseQueueByParsedFile(User $user, ParsedFile $parsedFile)
    {
        $status = new DownloadStatus();

        $cachedData = $this->getStatusData($user);

        $this->modelConverter->setData($cachedData, $status, true);

        $status->decreaseByParsedFile($parsedFile);

        $data = $this->modelConverter->convert($status);

        $this->setStatusData($user, $data);
    }

    /**
     * Sets status data
     *
     * @param User $user
     * @param $data
     */
    public function setStatusData(User $user, $data): void
    {
        $redisKey = $user->getDownloaderRedisKey();
        $redisData = (!is_string($data) ? json_encode($data) : $data);

        $this->redis->set($redisKey, $redisData);
    }

    /**
     * @param User $user
     * @return array
     * @throws NonUniqueResultException
     * @throws ReflectionException
     */
    public function getStatusData(User $user): array
    {
        $redisKey = $user->getDownloaderRedisKey();

        if ($this->redis->exists($redisKey)) {
            $redisData = $this->redis->get($redisKey);
            $cachedData = json_decode($redisData, true);
        } else {
            $cachedData = $this->createStatusData($user);
        }

        $this->updateQueuedFilesStatuses($cachedData);

        return $cachedData;
    }

    public function updateQueuedFilesStatuses(array &$cachedData = []): array
    {
        if ($cachedData['queuedFiles']) {
            foreach ($cachedData['queuedFiles'] as $queueKey => $queuedFile) {
                $statusKey = 'file_download_'.$queuedFile['identifier'];
                $status = $this->redis->exists($statusKey) ? $this->redis->get($statusKey) : 0;

                $cachedData['queuedFiles'][$queueKey]['status']['progress'] = (int)$status;
            }
        }

        return $cachedData;
    }
}
