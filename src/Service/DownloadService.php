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

                $result = (new DownloadedFile())
                    ->setResource($fileResource)
                    ->setFileEntity($fileEntity)
                    ->prepareTempFiles();

                if ($fileEntity->getType() === FileType::Image)
                    $result->optimize();

                $result->saveTargetFile();

                if ($result)
                    $downloadedFiles[] = $filesList[$fileKey];
            }
        }

        return $downloadedFiles;
    }

    /**
     * Prepares parsers objects defined in files list array;
     *
     * @param array $filesList
     * @param User $user
     */
    protected function prepareParsersForFiles(array $filesList, User $user): void
    {
        /** @var File $file */
        foreach ($filesList as $file) {
            if (!array_key_exists($file->getParser(), $this->parsers)) {
                $this->parsers[$file->getParser()] = $this->parserService->loadParser(
                    $file->getParser(),
                    $user
                );
            }
        }
    }
}