<?php

namespace App\Manager\Object;

use App\Converter\EntityConverter;
use App\Entity\Parser\File;
use App\Enum\FileStatus;
use App\Enum\FileType;
use App\Manager\Base\EntityManager;
use App\Manager\Base\ParserObjectManagerInterface;
use App\Manager\SettingsManager;
use App\Model\ParsedFile;
use App\Model\ParserRequestModel;
use App\Repository\FileRepository;
use App\Utils\FilesHelper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FileManager extends EntityManager
{
    protected $entityName = 'Parser\File';

    /** @var FileRepository */
    protected $repository;

    /** @var SettingsManager */
    protected $settingsManager;

    /** @var EntityConverter */
    protected $entityConverter;

    /** @required */
    public function init(SettingsManager $settingsManager, EntityConverter $entityConverter)
    {
        $this->settingsManager = $settingsManager;
        $this->entityConverter = $entityConverter;
    }

    /**
     * @param ParserRequestModel $parserRequestModel
     * @return ParserRequestModel
     */
    public function completeParsedStatuses(ParserRequestModel &$parserRequestModel) : ParserRequestModel
    {
        if ($parserRequestModel->files) {
            $imagesUrls = [];

            /* @var ParsedFile $file */
            foreach ($parserRequestModel->files as $file) {
                $imagesUrls[] = $file['url'];
            }

            $storedFilesArray = $this->repository->getQb()
                ->where('f.url IN (:filesUrls)')
                ->setParameter('filesUrls', $imagesUrls)
                ->getQuery()->getArrayResult();

            if ($storedFilesArray) {
                foreach ($parserRequestModel->files as $fileIndex => $file) {
                    $parserRequestModel->files[$fileIndex]['statuses'] = [];

                    foreach ($storedFilesArray as $storedFile) {
                        if ($storedFile['identifier'] == $file['identifier']) {
                            $parserRequestModel->files[$fileIndex]['statuses'][] = FileStatus::Queued;

                            if ($storedFile['downloadedAt']) {
                                $parserRequestModel->files[$fileIndex]['statuses'][] = FileStatus::Downloaded;
                            }
                        }
                    }
                }
            }
        }

        return $parserRequestModel;
    }

    /**
     * @param ParsedFile $parsedFile
     * @return bool
     * @throws \ReflectionException
     */
    public function toggleFileQueue(ParsedFile &$parsedFile): ParsedFile
    {
        $dbFile = $this->repository->findOneBy([
            'identifier' => $parsedFile->identifier,
            'parser' => $parsedFile->parser
        ]);

        if (!$dbFile) {
            $dbFile = new File();

            $this->entityConverter->setData($parsedFile, $dbFile);

            if ($this->save($dbFile)) {
                $parsedFile->statuses[] = FileStatus::Queued;
            }
        } else {
            $this->remove($dbFile);

            foreach ($parsedFile->statuses as $statusIndex => $status) {
                if ($status == FileStatus::Queued) {
                    unset($parsedFile->statuses[$statusIndex]);
                }
            }
        }

        return $parsedFile;
    }

    /**
     * Gets queued files (waiting for download);
     *
     * @param int $limit
     * @return array
     */
    public function getQueuedFiles(int $limit = 10) : array
    {
        $queuedFiles = $this->repository->getQueueFilesQb($limit)
            ->getQuery()
            ->getArrayResult();

        if ($queuedFiles) {
            foreach ($queuedFiles as $key => $queuedFile) {
                $queuedFiles[$key]['textSize'] = FilesHelper::bytesToSize($queuedFile['size']);
            }
        }

        return $queuedFiles;
    }

    /**
     * Return count of all queued files
     *
     * @return int
     */
    public function countQueuedFiles() : int
    {
        return $this->repository->countQueuedFiles();
    }

    /**
     * Return count of downloaded files (processed after specified date);
     *
     * @param int $startingTimestamp
     * @return int
     */
    public function countDownloadedFiles(int $startingTimestamp = 0) : int
    {
        return $this->repository->countDownloadedFiles($startingTimestamp);
    }

    /**
     * Downloading file (or package of files if size fits in the limit);
     *
     * @return array
     */
    public function downloadFilesPackage() : array
    {
        $sizeLimit = (5*1024*1024);
        $currentLimit = 0;
        $files = $this->repository->getQueueFilesQb(10)
            ->getQuery()->getResult();

        if ($files) {
            /** @var File $file */
            foreach ($files as $file) {
                $currentLimit += $file->getSize();

                $this->downloadFile($file);

                if ($currentLimit > $sizeLimit) {
                    break;
                }
            }
        }

        return [

        ];
    }

    public function downloadFile(File $file = null) : bool
    {
        if ($file) {
            $parserName = $file->getParser();
            $fileSettings = $this->processFileSettings($file);
            $parserSettings = $this->settingsManager->getParserSetting($parserName);

            if ($fileSettings->subfolder) {

            }

            if ($fileSettings->fileNamePattern) {

            }
        }

        return false;
    }

    protected function processFileSettings(File $file = null) : \stdClass
    {
        $rawSettings = $file->getBoard()->getSettings();
        $processedSettings = new \stdClass();
        $settingsKeys = ['subfolder', 'fileNamePattern'];

        foreach ($settingsKeys as $settingsKey) {
            $processedSettings->{$settingsKey} = $rawSettings[$settingsKey] ?? false;
        }

        return $processedSettings;
    }
}
