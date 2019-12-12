<?php

namespace App\Manager;

use App\Enum\DownloaderStatusType;
use App\Manager\Base\EntityManager;
use App\Manager\Object\FileManager;
use App\Model\ParserRequestModel;

class DownloadManager extends EntityManager
{
    private $session;

    /** @var FileManager $fileManager */
    private $fileManager;

    protected $entityName = 'Download';

    /**
     * @required
     */
    public function setManagers(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;

        return $this;
    }

    /**
     * Starts download
     *
     * @return bool
     */
    public function downloadStart()
    {
        $this->session->set('downloader_status', DownloaderStatusType::Downloading);
        $this->session->set('download_start', time());

        return true;
    }

    /**
     * Stops download
     *
     * @return bool
     */
    public function downloadStop()
    {
        $this->session->set('downloader_status', DownloaderStatusType::Idle);
        $this->session->set('download_end', time());

        return true;
    }

    /**
     * Gets download status data
     *
     * @return array
     */
    public function downloadStatus() : array
    {
        return [
            'queuedFiles' => $this->fileManager->countQueuedFiles(),
            'downloadedFiles' => $this->fileManager->countDownloadedFiles($this->session->get('download_start', 0)),
            'status' => $this->session->get('downloader_status'),
            'waitingFiles' => $this->fileManager->getQueueFiles(10)
        ];
    }

    public function downloadProcess() : array
    {
        $downloaderStatus = $this->session->get('downloader_status', DownloaderStatusType::Idle);

        if ($downloaderStatus == DownloaderStatusType::Downloading) {
            $result = $this->fileManager->downloadFilesPackage();
        } else {
            $result = [];
        }

        return $result;
    }
}
