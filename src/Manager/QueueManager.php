<?php

namespace App\Manager;

use App\Converter\ModelConverter;
use App\Enum\RedisKey;
use App\Factory\RedisFactory;
use App\Manager\Base\EntityManager;
use App\Manager\Object\FileManager;
use App\Model\Request\QueueRequest;
use App\Repository\FileRepository;
use App\Utils\FilesHelper;

class QueueManager extends EntityManager
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
        $this->redis = (new RedisFactory())->initializeConnection();
    }

    /**
     * Gets queued files (waiting for download);
     *
     * @param int $limit
     * @param bool $asArray
     * @return array
     */
    public function getQueuedFiles(QueueRequest $queueRequest): QueueRequest
    {
        $queuedFiles = $this->repository->getQueuedFiles($queueRequest->getProcessingFilesCount(), false);
        $queuedFilesCount = $this->repository->countAllQueuedFiles();
        $queuedFilesIds = [];

        if ($queuedFiles) {
            foreach ($queuedFiles as $key => $queuedFile) {
                $queuedFiles[$key]->setTextSize(FilesHelper::bytesToSize($queuedFile->getSize()));
                $queuedFilesIds[$queuedFile->getId()] = $queuedFile->getId();
            }
        }

        $this->redis->set(RedisKey::PreparedQueuedFilesListIds, $queuedFilesIds);
        $this->redis->set(RedisKey::PreparedQueuedFilesListCount, $queuedFilesCount);

        return $queuedFiles;
    }


}
