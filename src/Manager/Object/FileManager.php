<?php

namespace App\Manager\Object;

use App\Converter\EntityConverter;
use App\Converter\ModelConverter;
use App\Entity\Parser\File;
use App\Entity\Parser\Node;
use App\Entity\User;
use App\Enum\FileStatus;
use App\Enum\FileType;
use App\Factory\RedisFactory;
use App\Manager\Base\EntityManager;
use App\Manager\SettingsManager;
use App\Model\AbstractModel;
use App\Model\Download\DownloadStatus;
use App\Model\ParsedFile;
use App\Model\ParserRequest;
use App\Model\Status;
use App\Repository\FileRepository;
use App\Utils\FilesHelper;
use Doctrine\ORM\NonUniqueResultException;

class FileManager extends EntityManager
{
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

                            if ($storedFile['downloadedAt']) {
                                $parserRequest->files[$fileIndex]->addStatus(FileStatus::Downloaded);
                            }
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
            $this->entityConverter->setData($parsedFile, $file);
        }

        /** @var File $file */
        return $file;
    }

    /**
     * @param ParsedFile $parsedFile
     * @return bool
     * @throws \ReflectionException
     */
    public function addParsedFileToQueue(ParsedFile &$parsedFile, Node $parentNode = null): ParsedFile
    {
        $dbFile = new File();

        $this->entityConverter->setData($parsedFile, $dbFile);

        $dbFile->setParentNode($parentNode);

        if ($this->save($dbFile)) {
            $parsedFile->addStatus(FileStatus::Queued);
        }

        return $parsedFile;
    }

    public function removeParsedFileFromQueue(ParsedFile &$parsedFile, File $dbFile): ParsedFile
    {
        $this->remove($dbFile);

        $parsedFile->removeStatus(FileStatus::Queued);

        return $parsedFile;
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

    /**
     * Gets queued files (waiting for download);
     *
     * @param int $limit
     * @param bool $asArray
     * @return array
     */
    public function getQueuedFiles(int $limit = 10, bool $asArray = false) : array
    {
        $queuedFiles = $this->repository->getQueuedFiles($limit, $asArray);

        if ($queuedFiles) {
            foreach ($queuedFiles as $key => $queuedFile) {
                if ($asArray)
                    $queuedFiles[$key]['textSize'] = FilesHelper::bytesToSize($queuedFile->getSize());
                else
                    $queuedFiles[$key]->setTextSize(FilesHelper::bytesToSize($queuedFile->getSize()));
            }
        }

        return $queuedFiles;
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

        $data = $this->modelConverter->convert($downloadStatus);

        $this->redis->set($redisKey, json_encode($data));

        return $data;
    }
}
