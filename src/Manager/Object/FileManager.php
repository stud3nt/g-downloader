<?php

namespace App\Manager\Object;

use App\Converter\EntityConverter;
use App\Converter\ModelConverter;
use App\Entity\Parser\File;
use App\Entity\Parser\Node;
use App\Entity\User;
use App\Enum\FileStatus;
use App\Enum\NormalizationGroup;
use App\Enum\RedisKey;
use App\Factory\RedisFactory;
use App\Manager\Base\EntityManager;
use App\Manager\SettingsManager;
use App\Model\AbstractModel;
use App\Model\Download\DownloadStatus;
use App\Model\ParsedFile;
use App\Model\ParserRequest;
use App\Model\QueueSettings;
use App\Model\Status;
use App\Repository\FileRepository;
use App\Utils\FilesHelper;
use Doctrine\Common\Util\Debug;
use Doctrine\ORM\NonUniqueResultException;

class FileManager extends EntityManager
{
    const DownloadQueueRedisKey = 'download_queue';

    protected $entityName = 'Parser\File';

    /** @var FileRepository */
    protected $repository;

    /** @var SettingsManager */
    protected $settingsManager;

    /** @var EntityConverter */
    protected $entityConverter;

    /** @var ModelConverter */
    protected $modelConverter;

    /** @var \Predis\ClientInterface|\Redis|\RedisCluster */
    protected $redis;

    /** @required */
    public function init(SettingsManager $settingsManager, EntityConverter $entityConverter)
    {
        $this->settingsManager = $settingsManager;
        $this->entityConverter = $entityConverter;
        $this->modelConverter = (new ModelConverter());
        $this->redis = (new RedisFactory())->initializeConnection();
    }

    /**
     * @param ParserRequest|AbstractModel $parserRequest
     * @return ParserRequest
     */
    public function completeParsedStatuses(ParserRequest &$parserRequest) : ParserRequest
    {
        if ($parserRequest->files) {
            $filesIdentifiers = [];

            /* @var ParsedFile $file */
            foreach ($parserRequest->files as $file) {
                $filesIdentifiers[] = $file->getIdentifier();
            }

            $parserName = $parserRequest->currentNode->getParser();
            $storedFilesArray = $this->repository->getStoredFilesArray($parserName, $filesIdentifiers);

            if ($storedFilesArray) {
                foreach ($parserRequest->files as $fileIndex => $file) {
                    $parserRequest->files[$fileIndex]->clearStatuses();

                    foreach ($storedFilesArray as $storedFile) {
                        if ($storedFile['identifier'] == $file->getIdentifier()) {
                            $parserRequest->files[$fileIndex]->addStatus(FileStatus::Queued);

                            if ($storedFile['downloadedAt'])
                                $parserRequest->files[$fileIndex]->addStatus(FileStatus::Downloaded);

                            if ($storedFile['corrupted'])
                                $parserRequest->files[$fileIndex]->addStatus(FileStatus::Corrupted);
                        }
                    }
                }
            }
        }

        return $parserRequest;
    }

    /**
     * @param ParsedFile $parsedFile
     * @param boolean $createIfNotExists - if true, entity will be created (if not exists);
     * @return File|null
     * @throws \ReflectionException
     */
    public function getFileEntityByParsedFile(ParsedFile $parsedFile, bool $createIfNotExists = false): ?File
    {
        $file = $this->repository->findOneBy([
            'identifier' => $parsedFile->identifier,
            'parser' => $parsedFile->parser
        ]);

        if (!$file && $createIfNotExists) {
            $file = new File();
            $this->objectSerializer->deserialize($parsedFile, $file);
        }

        /** @var File $file */
        return $file;
    }

    /**
     * @param ParsedFile $parsedFile
     * @return bool
     * @throws \ReflectionException
     */
    public function addParsedFileToQueue(ParsedFile $parsedFile, Node $parentNode = null): ParsedFile
    {
        $dbFile = $this->objectSerializer->deserialize($parsedFile, File::class);
        $dbFile->setParentNode($parentNode);

        if ($this->save($dbFile)) {
            $parsedFile->addStatus(FileStatus::Queued);
        }

        return $parsedFile;
    }

    public function removeParsedFileFromQueue(ParsedFile $parsedFile, File $dbFile): ParsedFile
    {
        $this->remove($dbFile);

        $parsedFile->removeStatus(FileStatus::Queued);

        return $parsedFile;
    }

    /**
     * Get queue data
     *
     * @param int $downloadingFilesCount (default 6)
     * @return array|null
     */
    public function getQueueData(int $downloadingFilesCount = 6): ?array
    {
        if ($this->redis->exists(FileManager::DownloadQueueRedisKey)) { // get data from redis cache
            return json_decode($this->redis->get(FileManager::DownloadQueueRedisKey), true);
        }

        $queueFiles = $this->repository->getFilesQb(['status' => FileStatus::Queued])
            ->setMaxResults($downloadingFilesCount)
            ->orderBy('f.id', 'ASC')
            ->getQuery()
            ->getResult();

        if ($queueFiles) {
            foreach ($queueFiles as $fileIndex => $queueFile) {
                $queueFile->setStatus($queueFile->getStatus()->setDescription(FileStatus::Queued));
                $queueFiles[$fileIndex] = $queueFile;
            }

            $queuedFilesJson = $this->objectSerializer->serialize($queueFiles, 'json');
            $queuedFilesArray = json_decode($queuedFilesJson, true);

            $this->redis->set(FileManager::DownloadQueueRedisKey, $queuedFilesJson);
        }

        return $queuedFilesArray;
    }

    public function getFilesForDownload(int $limit = 6)
    {
        $queuedFilesIds = $this->redis->get(RedisKey::PreparedQueuedFilesListIds);
        $filesIds = [];

        foreach ($queuedFilesIds as $filesId) {
            $filesIds[] = $filesId;

            if (count($filesIds) >= $limit) {
                break;
            }
        }

        return $this->repository->getFilesQb(['status' => FileStatus::Queued, 'limit' => $limit])
            ->where('f.id IN (:ids)', implode(',', $filesIds))
            ->getQuery()
            ->getResult();
    }

    /**
     * Gets queued files data (count and size);
     *
     * @return \stdClass
     * @throws NonUniqueResultException
     */
    public function getQueuedFilesData(): \stdClass
    {
        $queuedData = $this->repository->getFilesCountData(FileStatus::Queued);

        $queuedObject = new \stdClass();
        $queuedObject->count = $queuedData['totalCount'];
        $queuedObject->size = $queuedData['totalSize'];

        return $queuedObject;
    }

    /**
     * Gets downloaded files data (count and size);
     *
     * @return \stdClass
     * @throws NonUniqueResultException
     */
    public function getDownloadedFilesData(): \stdClass
    {
        $downloadedData = $this->repository->getFilesCountData(FileStatus::Downloaded);

        $downloadedObject = new \stdClass();
        $downloadedObject->count = $downloadedData['totalCount'];
        $downloadedObject->size = $downloadedData['totalSize'];

        return $downloadedObject;
    }

    /**
     * @param ParsedFile $file
     * @return Status
     * @throws \ReflectionException
     */
    public function getFileDownloadStatus(ParsedFile $file): Status
    {
        $status = new Status();

        if ($this->redis->exists($file->getRedisPreviewKey())) {
            $status->setProgress(
                $this->redis->get(
                    $file->getRedisPreviewKey()
                )
            );
        }

        return $status;
    }

    public function getPotentialDuplicates(File $file): array
    {
        return $this->repository->getSimilarFiles($file);
    }

    /**
     * @param User $user
     * @return array
     * @throws NonUniqueResultException
     * @throws \ReflectionException
     */
    public function setStatusData(User $user): ?array
    {
        $redisKey = 'downloader_data_'.$user->getApiToken();

        $queuedCounts = $this->repository->getFilesCountData(FileStatus::Queued);
        $downloadedCounts = $this->repository->getFilesCountData(FileStatus::Downloaded);

        $downloadStatus = (new DownloadStatus())
            ->setQueuedFilesCount($queuedCounts['totalCount'])
            ->setQueuedFilesSize(FilesHelper::bytesToSize($queuedCounts['totalSize']))
            ->setDownloadedFilesCount($downloadedCounts['totalCount'])
            ->setDownloadedFilesSize(FilesHelper::bytesToSize($downloadedCounts['totalSize']))
        ;

        $data = $this->objectSerializer->serialize($downloadStatus);

        $this->redis->set($redisKey, json_encode($data));

        return $data;
    }
}
