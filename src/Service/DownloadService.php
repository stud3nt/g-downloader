<?php

namespace App\Service;

use App\Entity\Parser\File;
use App\Entity\User;
use App\Enum\FileType;
use App\Model\Download\DownloadedFile;

class DownloadService
{
    /** @var ParserService */
    protected $parserService;

    private $parsers = [];

    public function __construct(ParserService $parserService)
    {
        $this->parserService = $parserService;
    }

    /**
     * @param array $filesList
     * @param User $user
     * @return array
     * @throws \Exception
     */
    public function downloadQueuedParserFiles(array $filesList = [], User $user = null): array
    {
        $downloadedFiles = [];

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
                $fileEntity = $filesList[$fileKey];

                if ($this->downloadFile($fileEntity, $fileResource))
                    $downloadedFiles[] = $filesList[$fileKey];
            }
        }

        return $downloadedFiles;
    }

    /**
     * @param File $file
     * @param User $user
     * @throws \Exception
     * @return bool
     */
    public function downloadFileByEntity(File &$file, User $user): bool
    {
        $parser = $this->parserService->loadParser($file->getParser(), $user);
        $parser->prepareFileTargetDirectories($file);

        if (!file_exists($file->getTempFilePath()) || ((filesize($file->getTempFilePath()) * 0.8) < $file->getSize())) {
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
    protected function downloadFile(File $file, $fileResource): bool
    {
        $result = (new DownloadedFile())
            ->setFileEntity($file)
            ->setResource($fileResource)
            ->prepareTempFiles();

        if ($file->getType() === FileType::Image)
            $result->optimize();

        return $result->saveTargetFile();
    }
}