<?php

namespace App\Model\Download;

use App\Annotation\ModelVariable;
use App\Enum\DownloaderStatus;
use App\Model\AbstractModel;
use App\Model\ParsedFile;
use App\Utils\FilesHelper;

class DownloadStatus extends AbstractModel
{
    /**
     * @var string
     * @ModelVariable(type="string")
     */
    public $status = DownloaderStatus::Idle;

    /**
     * @var integer
     * @ModelVariable(type="integer")
     */
    public $queuedFilesCount = 0;

    /**
     * @var integer
     * @ModelVariable(type="integer")
     */
    public $queuedFilesSize = 0;

    /**
     * @var string
     * @ModelVariable(type="string")
     */
    public $queuedFilesTextSize = null;

    /**
     * @var integer
     * @ModelVariable(type="integer")
     */
    public $downloadedFilesCount = 0;

    /**
     * @var integer
     * @ModelVariable(type="integer")
     */
    public $downloadedFilesSize = 0;

    /**
     * @var string
     * @ModelVariable(type="string")
     */
    public $downloadedFilesTextSize = null;

    /**
     * @var ParsedFile[]
     * @ModelVariable(converter="Model", converterOptions={"class":"App\Model\ParsedFile"}, type="array")
     */
    public $queuedFiles = [];

    public function increaseByParsedFile(ParsedFile $parsedFile)
    {
        $this->queuedFilesCount++;
        $this->queuedFilesSize += $parsedFile->getSize();
        $this->queuedFilesTextSize = FilesHelper::bytesToSize(
            $this->queuedFilesSize
        );
    }

    public function decreaseByParsedFile(ParsedFile $parsedFile)
    {
        $this->queuedFilesCount--;
        $this->queuedFilesSize -= $parsedFile->getSize();
        $this->queuedFilesTextSize = FilesHelper::bytesToSize(
            $this->queuedFilesSize
        );
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return $this;
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return int
     */
    public function getQueuedFilesCount(): int
    {
        return $this->queuedFilesCount;
    }

    /**
     * @param int $queuedFilesCount
     * @return $this;
     */
    public function setQueuedFilesCount(int $queuedFilesCount): self
    {
        $this->queuedFilesCount = $queuedFilesCount;

        return $this;
    }

    /**
     * @return ParsedFile[]
     */
    public function getQueuedFiles(): array
    {
        return $this->queuedFiles;
    }

    public function getQueuedFileByIdentifier(string $identifier): ?ParsedFile
    {
        if ($this->queuedFiles) {
            foreach ($this->queuedFiles as $queuedFile) {
                if ($queuedFile->getIdentifier() === $identifier)
                    return $queuedFile;
            }
        }

        return null;
    }

    /**
     * @param ParsedFile[] $queuedFiles
     * @return $this;
     */
    public function setQueuedFiles(array $queuedFiles): self
    {
        $this->queuedFiles = $queuedFiles;

        return $this;
    }

    /**
     * @return DownloadStatus
     */
    public function clearQueuedFiles(): self
    {
        $this->queuedFiles = [];

        return $this;
    }

    /**
     * @param ParsedFile $queuedFile
     * @return DownloadStatus
     */
    public function addQueuedFile(ParsedFile $queuedFile): self
    {
        $this->queuedFiles[] = $queuedFile;

        return $this;
    }

    /**
     * @return int
     */
    public function getQueuedFilesSize(): int
    {
        return $this->queuedFilesSize;
    }

    /**
     * @param int $queuedFilesSize
     * @return DownloadStatus
     */
    public function setQueuedFilesSize(int $queuedFilesSize): self
    {
        $this->queuedFilesSize = $queuedFilesSize;

        return $this;
    }

    /**
     * @return string
     */
    public function getQueuedFilesTextSize(): string
    {
        return $this->queuedFilesTextSize;
    }

    /**
     * @param string $queuedFilesTextSize
     * @return DownloadStatus
     */
    public function setQueuedFilesTextSize(string $queuedFilesTextSize): self
    {
        $this->queuedFilesTextSize = $queuedFilesTextSize;

        return $this;
    }

    /**
     * @return int
     */
    public function getDownloadedFilesCount(): int
    {
        return $this->downloadedFilesCount;
    }

    /**
     * @param int $downloadedFilesCount
     * @return DownloadStatus
     */
    public function setDownloadedFilesCount(int $downloadedFilesCount): self
    {
        $this->downloadedFilesCount = $downloadedFilesCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getDownloadedFilesSize(): int
    {
        return $this->downloadedFilesSize;
    }

    /**
     * @param int $downloadedFilesSize
     * @return DownloadStatus
     */
    public function setDownloadedFilesSize(int $downloadedFilesSize): self
    {
        $this->downloadedFilesSize = $downloadedFilesSize;

        return $this;
    }

    /**
     * @return string
     */
    public function getDownloadedFilesTextSize(): string
    {
        return $this->downloadedFilesTextSize;
    }

    /**
     * @param string $downloadedFilesTextSize
     * @return DownloadStatus
     */
    public function setDownloadedFilesTextSize(string $downloadedFilesTextSize): self
    {
        $this->downloadedFilesTextSize = $downloadedFilesTextSize;

        return $this;
    }
}