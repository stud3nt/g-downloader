<?php

namespace App\Parser;

use App\Entity\Parser\File;
use App\Enum\FileIcon;
use App\Enum\NodeLevel;
use App\Enum\ParserType;
use App\Factory\RedisFactory;
use App\Model\ParsedFile;
use App\Model\ParsedNode;
use App\Model\ParserRequest;
use App\Parser\Base\AbstractParser;
use App\Utils\FilesHelper;
use App\Utils\UrlHelper;
use PHPHtmlParser\Dom\HtmlNode;
use stringEncode\Exception;

class Boards4chanParser extends AbstractParser
{
    protected $parserName = ParserType::Boards4chan;

    protected $mainBoardUrl = 'http://4chan.org/';

    protected $mainGalleryUrl = 'http://boards.4chan.org/';

    protected $mainMediaUrl = 'https://i.4cdn.org/';

    /**
     * @param int $page
     * @param array $options
     * @return array
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function getBoardsListData(ParserRequest $parserRequest) : ParserRequest
    {
        $parserRequest->clearParsedData();

        $cachedRequest = $this->getParserCache($parserRequest);

        if ($cachedRequest) {
            $parserRequest->setParsedNodes($cachedRequest->getParsedNodes())
                ->setPagination($cachedRequest->getPagination())
                ->getStatus()
                ->updateProgress(50, "READING FROM CACHE...")
                ->send();
        } else {
            $parserRequest->getStatus()
                ->updateProgress(30)
                ->send();

            /** @var HtmlNode $column **/
            /** @var HtmlNode $anchor **/
            $dom = $this->loadDomFromUrl($this->mainBoardUrl);
            $name = $dom->find('title')[0]->text();

            $parserRequest->getPagination()->disable();
            $parserRequest->getCurrentNode()
                ->setUrl($this->mainBoardUrl)
                ->setName($name, true)
                ->setLabel($name);

            $domColumns = $dom->find('div.column');

            $parserRequest->getStatus()
                ->updateProgress(50)
                ->send();

            foreach ($domColumns as $column) {
                if ($column->find('h3')->text() === 'Adult') {
                    foreach ($column->find('a.boardlink') as $anchor) {
                        $parserRequest->addParsedNode(
                            (new ParsedNode(ParserType::Boards4chan, NodeLevel::Board))
                                ->setName($anchor->text())
                                ->setUrl('https:'.$anchor->getAttribute('href').'catalog')
                                ->setIdentifier($this->getBoardSymbol($anchor->getAttribute('href')))
                                ->setNoImage(true)
                        );
                    }
                }
            }

            $this->setParserCache($parserRequest, 0);
        }

        return $parserRequest;
    }

    /**
     * @param ParserRequest $parserRequest
     * @return ParserRequest
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function getBoardData(ParserRequest $parserRequest) : ParserRequest
    {
        $parserRequest->clearParsedData();
        $parserRequest->getPagination()->disable();

        $this->updateUrlsFromBoardUrls($parserRequest->getCurrentNode()->getUrl());

        $cachedRequest = $this->getParserCache($parserRequest);

        if ($cachedRequest) {
            $parserRequest->setParsedNodes($cachedRequest->getParsedNodes())
                ->setPagination($cachedRequest->getPagination())
                ->getStatus()
                ->updateProgress(50, "READING FROM CACHE...")
                ->send();
        } else {
            $html = $this->loadHtmlFromUrl($parserRequest->getCurrentNode()->getUrl());

            $parserRequest->getStatus()
                ->updateProgress(20)
                ->send();

            if ($html) {
                $stringStart = ';var catalog = ';
                $stringEnd = ';var style_group = ';
                $jsonStart = strpos($html, $stringStart) + strlen($stringStart);
                $jsonEnd = strpos($html, $stringEnd);
                $jsonContent = substr($html, $jsonStart, ($jsonEnd-$jsonStart));
                $arrayContent = json_decode($jsonContent, true);
                $dom = $this->loadDomFromHTML($html);

                $name = $dom->find('title')[0]->text();
                $parserRequest->getCurrentNode()
                    ->setName($name, true)
                    ->setLabel($name);

                if ($arrayContent['threads']) {
                    $parserRequest->getStatus()
                        ->startSteppedProgress('get_board_data', count($arrayContent['threads']), 20, 90);

                    foreach ($arrayContent['threads'] as $galleryId => $galleryData) {
                        if ((int)$galleryData['i'] <= 2) {
                            $parserRequest->getStatus()->executeSteppedProgressStep('get_board_data');
                            continue; // skip empty threads
                        }

                        $name = trim($galleryData['sub']);
                        $description = trim($galleryData['teaser']);

                        $node = (new ParsedNode(ParserType::Boards4chan, NodeLevel::Gallery))
                            ->setIdentifier($galleryId)
                            ->setName(strlen($name) > 0 ? $name : $description)
                            ->setDescription(strlen($name) > 0 ? $description : '')
                            ->setUrl($this->mainGalleryUrl.'thread/'.$galleryId)
                            ->setImagesNo($galleryData['i'])
                            ->setCommentsNo($galleryData['r'])
                        ;

                        if (isset($galleryData['imgurl'])) {
                            $imageFilename = $galleryData['imgurl'].'s.jpg';

                            $webThumbnailUrl = $this->mainMediaUrl.$imageFilename;
                            $localThumbnailUrl = $this->thumbnailTempDir.$imageFilename;

                            if (!file_exists($localThumbnailUrl)) {
                                if (!$this->downloadFile($webThumbnailUrl, $localThumbnailUrl)) {
                                    throw new Exception("ERROR IN THUMBNAIL DOWNLOADING");
                                }
                            }

                            $node->addLocalThumbnail(
                                UrlHelper::prepareLocalUrl($localThumbnailUrl)
                            );
                        }

                        $parserRequest->addParsedNode($node);
                        $parserRequest->getStatus()->executeSteppedProgressStep('get_board_data');

                        if ($this->testGalleriesLimitReached(count($parserRequest->getParsedNodes())))
                            break;
                    }
                }

                $parserRequest->getStatus()->endSteppedProgress('get_board_data');
            }

            $this->setParserCache($parserRequest, 180);
        }

        $parserRequest->getCurrentNode()
            ->setAllowCategory(false)
            ->setAllowTags(false);

        return $parserRequest;
    }

    /**
     * Extracting file information (width, height, size) from string (eq. "(188 KB, 960x960)")
     *
     * @param string $text
     * @return array
     */
    private function extractFileInfoFromText(string $text)
    {
        $clearText = str_replace(['File:  (', ')'], ['', ''], $text);
        $textArray = explode(', ', $clearText);
        $sizeArray = explode('x', $textArray[1]);

        return [
            'textSize' => $textArray[0],
            'width' => $sizeArray[0],
            'height' => $sizeArray[1]
        ];
    }

    /**
     * @param ParserRequest $parserRequest
     * @return ParserRequest
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function getGalleryData(ParserRequest $parserRequest) : ParserRequest
    {
        $parserRequest->clearParsedNodes()
            ->getCurrentNode()
            ->setAllowCategory(true)
            ->setAllowTags(true);

        $cachedRequest = $this->getParserCache($parserRequest);

        if ($cachedRequest) {
            $parserRequest->setFiles($cachedRequest->getFiles())
                ->setPagination($cachedRequest->getPagination())
                ->getStatus()
                ->updateProgress(50, "READING FROM CACHE...")
                ->send();
        } else {
            $parserRequest->getPagination()->disable();

            /** @var HtmlNode $anchor */
            /** @var HtmlNode $image */
            /** @var HtmlNode $div */
            $dom = $this->loadDomFromUrl($parserRequest->getCurrentNode()->getUrl());
            $divs = $dom->getElementsByClass('postContainer');



            $galleryName = $dom->find('title')[0]->text();
            $parserRequest->getCurrentNode()
                ->setName($galleryName, true)
                ->setLabel($galleryName);

            $parserRequest->getStatus()
                ->startSteppedProgress('get_gallery_data', count($divs), 20, 90);

            foreach ($divs as $div) {
                if (in_array($div->getAttribute('class'), ['postContainer replyContainer', 'postContainer opContainer'])) {
                    foreach ($div->find('a') as $anchor) {
                        if ($anchor->getAttribute('class') == 'fileThumb') { // filethumb
                            $thumbnail = $anchor->find('img'); // thumb image;
                            $imageUrl = 'http:'.$anchor->getAttribute('href');
                            $thumbnailUrl = 'http:'.$thumbnail->getAttribute('src');

                            $fileData = $this->extractFileInfoFromText($div->find('div.fileText')->text());
                            $date = (new \DateTime())->setTimeZone(new \DateTimeZone('UTC'))
                                ->setTimestamp(
                                    (int)$div->find('span.dateTime')->getAttribute('data-utc')
                                )
                                ->setTimezone(new \DateTimeZone('Europe/Warsaw'));

                            $parsedFile = (new ParsedFile(ParserType::Boards4chan, FilesHelper::getFileType($imageUrl, true)))
                                ->setIdentifier(FilesHelper::getFileName($imageUrl))
                                ->setName(FilesHelper::getFileName($imageUrl))
                                ->setExtension(FilesHelper::getFileExtension($imageUrl))
                                ->setMimeType(FilesHelper::getFileMimeType($imageUrl, true))
                                ->setUrl($imageUrl)
                                ->setFileUrl($imageUrl)
                                ->setIcon(FileIcon::Boards4Chan)
                                //->setThumbnail($thumbnailUrl) ==> web thumbnail is available, but often blocked
                                ->setSize(FilesHelper::sizeToBytes($fileData['textSize']))
                                ->setTextSize($fileData['textSize'])
                                ->setWidth($fileData['width'])
                                ->setHeight($fileData['height'])
                                ->setUploadedAt($date)
                            ;

                            $localThumbnailUrl = $this->thumbnailTempDir.FilesHelper::getFileName($thumbnailUrl, true);

                            if (!file_exists($localThumbnailUrl) || $parserRequest->isIgnoreCache()) {
                                if (!$this->downloadFile($thumbnailUrl, $localThumbnailUrl)) {
                                    throw new Exception("ERROR IN THUMBNAIL DOWNLOADING");
                                }
                            }

                            $parsedFile->setLocalThumbnail(UrlHelper::prepareLocalUrl($localThumbnailUrl));

                            $parserRequest->addFile($parsedFile);
                        }
                    }
                }

                $parserRequest->getStatus()->executeSteppedProgressStep('get_gallery_data');
                usleep(200);

                if ($this->testGalleryImagesLimitReached(count($parserRequest->getFiles())))
                    break;
            }

            $parserRequest->getStatus()->endSteppedProgress('get_gallery_data');

            $this->setParserCache($parserRequest, 300);
        }

        return $parserRequest;
    }

    public function getFilePreview(ParsedFile $parsedFile) : ParsedFile
    {
        $this->clearFileCache();

        $previewFilePath = $this->previewTempDir.$parsedFile->getFullFilename();
        $previewWebPath = $this->previewTempFolder.$parsedFile->getFullFilename();

        $parsedFile->setLocalUrl($previewWebPath);
        $parsedFile->setPreviewFilePath($previewFilePath);

        if (!file_exists($previewFilePath)) {
            $redis = (new RedisFactory())->initializeConnection();

            $this->downloadFile(
                $parsedFile->getUrl(),
                $previewFilePath,
                function ($resource, $downloadSize, $downloaded, $uploadSize, $uploaded) use ($parsedFile, $redis) {
                    if ($downloadSize > 0) {
                        $redis->set($parsedFile->getRedisPreviewKey(), round(($downloaded / $downloadSize) * 100));
                        $redis->expire($parsedFile->getRedisPreviewKey(), 10);
                    } elseif ($downloadSize === 0) { // no size and progress returned from boards4chan :/
                        $redis->set($parsedFile->getRedisPreviewKey(), 50);
                        $redis->expire($parsedFile->getRedisPreviewKey(), 10);
                    }
                }
            );

            $redis->set($parsedFile->getRedisPreviewKey(), 100);
            $redis->expire($parsedFile->getRedisPreviewKey(), 10);
        }

        return $parsedFile;
    }

    public function determineFileSubfolder(File $file): ?string
    {
        $subfolder = '';

        if ($gallery = $file->getParentNode()) {
            if ($board = $gallery->getParentNode()) {
                $subfolder = DIRECTORY_SEPARATOR.FilesHelper::folderNameFromString($board->getName());
            }
        }

        return $subfolder;
    }

    protected function getBoardSymbol(string $boardUrl) : string
    {
        $urlArray = explode('/', $boardUrl);
        $reversedUrlArray = array_reverse($urlArray);

        foreach ($reversedUrlArray as $key => $urlElement) {
            if (strlen(trim($urlElement)) > 0) {
                return trim($urlElement);
            }
        }

        return '';
    }

    /**
     * Extracts board name fragment from URL and sets it to main addresses;
     *
     * @param string $boardUrl
     */
    protected function updateUrlsFromBoardUrls(string $boardUrl) : void
    {
        $urlArray = explode('/', $boardUrl);
        $reversedUrlArray = array_reverse($urlArray);

        foreach ($reversedUrlArray as $key => $urlElement) {
            if ($urlElement === 'catalog') {
                $targetUrlElement = $reversedUrlArray[$key + 1];
                $this->mainMediaUrl .= $targetUrlElement.'/';
                $this->mainGalleryUrl .= $targetUrlElement.'/';
                break;
            }
        }
    }
}