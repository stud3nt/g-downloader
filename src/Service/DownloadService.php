<?php


namespace App\Service;

use App\Entity\Parser\File;
use App\Enum\FileType;
use App\Enum\ParserType;
use App\Model\Downloaded\ParsedImage;
use App\Utils\FilesHelper;
use Gregwar\Image\Image;

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
     * @return array
     * @throws \Exception
     */
    public function downloadQueuedParserFiles(array $filesList = []): array
    {
        $downloadedFiles = [];

        if ($filesList) {
            $this->prepareParsersForFiles($filesList);
            $curlService = new CurlRequest();

            /** @var File $fileEntity */
            foreach ($filesList as $fileKey => $fileEntity) {
                $this->parsers[$fileEntity->getParser()]->generateFileCurlRequest($fileEntity);
                $curlService->addRequest($fileEntity->getCurlRequest(), $fileKey);
            }

            $response = $curlService->executeRequests(); // executing download curls;

            foreach ($response as $fileKey => $fileResource) {
                switch ($filesList[$fileKey]->getType()) {
                    case FileType::Image:
                        $result = (new ParsedImage())
                            ->setResource($fileResource)
                            ->setFileEntity($filesList[$fileKey])
                            ->prepareTempFiles()
                            ->optimize()
                            ->saveTargetFile()
                        ;
                        break;

                    case FileType::Video:
                        
                        break;
                }

                if ($result) {
                    $downloadedFiles[] = $filesList[$fileKey];
                }
            }
        }

        return $downloadedFiles;
    }

    /**
     * Prepares parsers objects defined in files list array;
     *
     * @param array $filesList
     */
    protected function prepareParsersForFiles(array $filesList): void
    {
        /** @var File $file */
        foreach ($filesList as $file) {
            if (!array_key_exists($file->getParser(), $this->parsers)) {
                $this->parsers[$file->getParser()] = $this->parserService->loadParser($file->getParser());
            }
        }
    }
}