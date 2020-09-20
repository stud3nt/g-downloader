<?php

namespace App\Service;

use App\Entity\Parser\File;
use App\Entity\User;
use App\Enum\FileType;
use App\Manager\Object\FileManager;
use App\Model\Download\DownloadedFile;

class DownloadService
{
    /** @var ParserService */
    protected $parserService;

    /** @var FileManager */
    protected $fileManager;

    private $parsers = [];

    public function __construct(ParserService $parserService, FileManager $fileManager)
    {
        $this->parserService = $parserService;
        $this->fileManager = $fileManager;
    }

    /**
     * @param array $filesList
     * @param User $user
     * @return array
     * @throws \Exception
     */
    public function downloadQueuedParserFiles(?array $filesList, ?User $user): int
    {
        $downloadFileCount = 0;

        if ($filesList && $user) {
            $this->prepareParsersForFiles($filesList, $user);
            $curlService = new CurlRequest();

            /** @var File $fileEntity */
            foreach ($filesList as $fileKey => $fileEntity) {
                $this->parsers[$fileEntity->getParser()]->generateFileCurlRequest($fileEntity);
                $curlService->addRequest($fileEntity->getCurlRequest(), $fileKey);
            }

            $response = $curlService->executeRequests(); // executing download curls;

            foreach ($response as $fileKey => $fileResource) {
                $downloadedFile = $this->downloadFile($filesList[$fileKey], $fileResource);

                if ($downloadedFile) {
                    $downloadFileCount++;

                    if (!$downloadedFile->getDuplicateOf()) {
                        $downloadedFile->setDownloadedAt(new \DateTime('now'));
                    }

                    $this->fileManager->save($downloadedFile);


                }
            }
        }

        return $downloadFileCount;
    }

    /**
     * Downloaded single file based on his entity;
     *
     * @param File $file
     * @param User $user
     * @throws \Exception
     * @return bool
     */
    public function downloadFileByEntity(File &$file, User $user): ?File
    {
        $parser = $this->parserService->loadParser($file->getParser(), $user);
        $parser->prepareFileTargetDirectories($file);

        if (!file_exists($file->getTempFilePath()) || // if file not exists
            (
                file_exists($file->getTempFilePath()) && // or if file exists AND:
                (
                    (filesize($file->getTempFilePath()) < (20 * 1024)) // error file saving (smaller than 20KB)
                        ||
                    ($file->getSize() > 0 && (filesize($file->getTempFilePath()) * 1.2) < $file->getSize()) // OR optimized (must be downloaded again)
                )
            )
        ) {
            $fileResource = (new CurlRequest())->executeSingleRequest(
                $parser->generateFileCurlRequest($file)->getCurlRequest()
            );
        } else {
            $fileResource = file_get_contents($file->getTempFilePath());
        }

        return $this->downloadFile($file, $fileResource);
    }

    /**
     * Prepares parsers objects defined in files list array;
     *
     * @param array $filesList
     * @param User $user
     */
    public function prepareParsersForFiles(array $filesList, User $user): void
    {
        /** @var File $file */
        foreach ($filesList as $file) {
            if (!array_key_exists($file->getParser(), $this->parsers)) {
                $this->parsers[$file->getParser()] = $this->parserService->loadParser($file->getParser(), $user);
            }
        }

        return;
    }

    /**
     * @param File $file
     * @param $fileResource
     * @return bool
     * @throws \Exception
     */
    protected function downloadFile(File $file, $fileResource): ?File
    {
        $downloadedFile = (new DownloadedFile())
            ->setFileEntity($file)
            ->setResource($fileResource)
            ->prepareTempFiles()
            ->analyseTempFiles();

        $downloadedFile->analysePotentialDuplicates(
            $this->fileManager->getPotentialDuplicates(
                $downloadedFile->getFileEntity()
            )
        );

        if ($file->getType() === FileType::Image)
            $downloadedFile->optimizeImage();

        if ($downloadedFile->saveTargetFile())
            return $downloadedFile->getFileEntity();

        return null;
    }
}