<?php

namespace App\Parser;

use App\Enum\NodeLevel;
use App\Enum\ParserType;
use App\Model\ParsedFile;
use App\Model\ParsedNode;
use App\Model\ParserRequest;
use App\Parser\Base\AbstractParser;
use App\Parser\Base\ParserInterface;
use App\Converter\EntityConverter;
use App\Utils\FilesHelper;
use App\Utils\UrlHelper;
use PHPHtmlParser\Dom\HtmlNode;
use stringEncode\Exception;

class Boards4chanParser extends AbstractParser implements ParserInterface
{
    protected $parserName = ParserType::Boards4chan;

    protected $mainBoardUrl = 'http://4chan.org/';

    protected $mainGalleryUrl = 'http://boards.4chan.org/';

    protected $mainMediaUrl = 'https://i.4cdn.org/';

    /** @var EntityConverter */
    protected $entityConverter;

    /** @required */
    public function setEntityConverter(EntityConverter $entityConverter)
    {
        $this->entityConverter = $entityConverter;
    }

    public function getOwnersList(ParserRequest &$parserRequest): ParserRequest
    {
        // NOTHING TO DO HERE
        return $parserRequest;
    }

    /**
     * @param int $page
     * @param array $options
     * @return array
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function getBoardsListData(ParserRequest &$parserRequest) : ParserRequest
    {
        if (!$this->getParserCache($parserRequest)) {
            // @var HtmlNode $column
            // @var HtmlNode $anchor
            $dom = $this->loadDomFromUrl($this->mainBoardUrl);

            $parserRequest->currentNode->setUrl($this->mainBoardUrl);
            $parserRequest->currentNode->setName($dom->find('title')[0]->text());
            $parserRequest->pagination->disable();

            $domColumns = $dom->find('div.column');

            $this->setPageLoaderProgress(50);

            foreach ($domColumns as $column) {
                if ($column->find('h3')->text() === 'Adult') {
                    foreach ($column->find('a.boardlink') as $anchor) {
                        $parserRequest->parsedNodes[] = (new ParsedNode(ParserType::Boards4chan, NodeLevel::BoardsList))
                            ->setName($anchor->text())
                            ->setUrl('https:'.$anchor->getAttribute('href').'catalog')
                            ->setIdentifier($this->getBoardSymbol($anchor->getAttribute('href')))
                            ->setNoImage(true)
                        ;
                    }
                }
            }

            $this->setParserCache($parserRequest, 0);
            $this->setPageLoaderProgress(100);
        }

        return $parserRequest;
    }

    /**
     * @param ParserRequest $parserRequest
     * @return ParserRequest
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function getBoardData(ParserRequest &$parserRequest) : ParserRequest
    {
        $parserRequest->pagination->disable();

        $this->updateUrlsFromBoardUrls($parserRequest->currentNode->getUrl());

        if (!$this->getParserCache($parserRequest)) {
            $html = $this->loadHtmlFromUrl($parserRequest->currentNode->getUrl());

            $this->setPageLoaderProgress(20);

            if ($html) {
                $stringStart = ';var catalog = ';
                $stringEnd = ';var style_group = ';
                $jsonStart = strpos($html, $stringStart) + strlen($stringStart);
                $jsonEnd = strpos($html, $stringEnd);
                $jsonContent = substr($html, $jsonStart, ($jsonEnd-$jsonStart));
                $arrayContent = json_decode($jsonContent, true);
                $dom = $this->loadDomFromHTML($html);

                $parserRequest->currentNode->setName($dom->find('title')[0]->text());

                if ($arrayContent['threads']) {
                    $this->startProgress('get_board_data', count($arrayContent['threads']), 20, 90);

                    foreach ($arrayContent['threads'] as $galleryId => $galleryData) {
                        if ((int)$galleryData['i'] <= 2) {
                            $this->progressStep('get_board_data');
                            continue; // skip empty threads
                        }

                        $name = trim($galleryData['sub']);
                        $description = trim($galleryData['teaser']);

                        $node = (new ParsedNode(ParserType::Boards4chan, NodeLevel::Board))
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

                        $parserRequest->parsedNodes[] = $node;
                        $this->progressStep('get_board_data');
                    }
                }

                $this->endProgress('get_board_data');
            }

            $this->setParserCache($parserRequest, 180);
        }

        return $parserRequest;
    }

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
    public function getGalleryData(ParserRequest &$parserRequest) : ParserRequest
    {
        if (!$this->getParserCache($parserRequest)) {
            $parserRequest->pagination->disable();

            /** @var HtmlNode $anchor */
            /** @var HtmlNode $image */
            /** @var HtmlNode $div */
            $dom = $this->loadDomFromUrl($parserRequest->currentNode->getUrl());
            $divs = $dom->getElementsByClass('postContainer');

            $parserRequest->currentNode->setName($dom->find('title')[0]->text());

            $this->setPageLoaderProgress(20);
            $this->startProgress('get_gallery_data', count($divs), 20, 90);

            foreach ($divs as $div) {
                if ($div->getAttribute('class') == 'postContainer replyContainer') {
                    foreach ($div->find('a') as $anchor) {
                        if ($anchor->getAttribute('class') == 'fileThumb') { // filethumb
                            $thumbnail = $anchor->find('img'); // thumb image;
                            $imageUrl = 'http:'.$anchor->getAttribute('href');
                            $thumbnailUrl = 'http:'.$thumbnail->getAttribute('src');

                            $fileData = $this->extractFileInfoFromText($div->find('div.fileText')->text());

                            $parsedFile = (new ParsedFile(ParserType::Boards4chan, FilesHelper::getFileType($imageUrl, true)))
                                ->setIdentifier(FilesHelper::getFileName($imageUrl))
                                ->setName(FilesHelper::getFileName($imageUrl))
                                ->setExtension(FilesHelper::getFileExtension($imageUrl))
                                ->setMimeType(FilesHelper::getFileMimeType($imageUrl, true))
                                ->setUrl($imageUrl)
                                ->setFileUrl($imageUrl)
                                //->setThumbnail($thumbnailUrl) ==> web thumbnail is available, but often blocked
                                ->setSize(FilesHelper::sizeToBytes($fileData['textSize']))
                                ->setTextSize($fileData['textSize'])
                                ->setWidth($fileData['width'])
                                ->setHeight($fileData['height'])
                            ;

                            $localThumbnailUrl = $this->thumbnailTempDir.FilesHelper::getFileName($thumbnailUrl, true);

                            if (!file_exists($localThumbnailUrl)) {
                                if (!$this->downloadFile($thumbnailUrl, $localThumbnailUrl)) {
                                    throw new Exception("ERROR IN THUMBNAIL DOWNLOADING");
                                }
                            }

                            $parsedFile->setLocalThumbnail(UrlHelper::prepareLocalUrl($localThumbnailUrl));

                            $this->setParserCache($parserRequest, 300);
                            $parserRequest->files[] = $parsedFile;
                        }
                    }
                }

                $this->progressStep('get_gallery_data');
            }
        }

        return $parserRequest;
    }

    /**
     * Nothing to do in this parser;
     *
     * @return array
     */
    public function getFileData(ParsedFile &$parsedFile) : ParsedFile
    {
        return $parsedFile; // nothing to do here;
    }

    public function getFilePreview(ParsedFile &$parsedFile) : ParsedFile
    {
        $this->clearCache();

        $previewFilePath = $this->previewTempDir.$parsedFile->getFullFilename();
        $previewWebPath = $this->previewTempFolder.$parsedFile->getFullFilename();

        if (!file_exists($previewFilePath)) {
            $this->downloadFile($parsedFile->getUrl(), $previewFilePath);
        }

        $parsedFile->setLocalUrl($previewWebPath);

        return $parsedFile;
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