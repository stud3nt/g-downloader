<?php

namespace App\Model\Request;

use App\Enum\DownloaderStatus;
use App\Model\AbstractModel;
use App\Model\ParsedFile;

class QueueRequest extends AbstractModel
{
    private string $status = DownloaderStatus::Idle;

    private int $processingFilesCount = 30;

    private int $totalFilesCount = 0;

    /** @var ParsedFile[] */
    private array $files = [];

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return QueueRequest
     */
    public function setStatus(string $status): QueueRequest
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalFilesCount(): int
    {
        return $this->totalFilesCount;
    }

    /**
     * @param int $totalFilesCount
     * @return QueueRequest
     */
    public function setTotalFilesCount(int $totalFilesCount): QueueRequest
    {
        $this->totalFilesCount = $totalFilesCount;
        return $this;
    }

    /**
     * @return ParsedFile[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @param ParsedFile[] $files
     * @return QueueRequest
     */
    public function setFiles(array $files): QueueRequest
    {
        $this->files = $files;
        return $this;
    }

    /**
     * @return int
     */
    public function getProcessingFilesCount(): int
    {
        return $this->processingFilesCount;
    }

    /**
     * @param int $processingFilesCount
     * @return QueueRequest
     */
    public function setProcessingFilesCount(int $processingFilesCount): QueueRequest
    {
        $this->processingFilesCount = $processingFilesCount;
        return $this;
    }
}