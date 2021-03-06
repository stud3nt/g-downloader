<?php

namespace App\Parser;

use App\Enum\FileIcon;
use App\Enum\FileType;
use App\Enum\NodeLevel;
use App\Enum\ParserType;
use App\Factory\RedisFactory;
use App\Model\ParsedFile;
use App\Model\ParsedNode;
use App\Model\ParserRequest;
use App\Parser\Base\AbstractParser;
use App\Utils\FilesHelper;
use App\Utils\StringHelper;
use App\Utils\UrlHelper;
use PHPHtmlParser\Dom\HtmlNode;

class ImagefapParser extends AbstractParser
{
    protected $parserName = ParserType::Imagefap;

    protected $mainBoardUrl = 'https://www.imagefap.com/';

    protected $mainGalleryUrl = 'https://x1.fap.to/';

    protected $mainMediaUrl = 'https://x.imagefapusercontent.com/';

    /**
     * Loading imagefap users list
     *
     * @param ParserRequest $parserRequest
     * @return ParserRequest
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function getOwnersList(ParserRequest $parserRequest) : ParserRequest
    {
        $cachedRequest = $this->getParserCache($parserRequest);

        if ($cachedRequest) {
            $parserRequest->setParsedNodes($cachedRequest->getParsedNodes())
                ->setPagination($cachedRequest->getPagination())
                ->getStatus()
                ->updateProgress(50, 'Loading from cache...')
                ->send();
        } else {
            $pagination = $parserRequest->getPagination();
            $urlParams = array_merge($parserRequest->getSorting(), [
                'page' => ($pagination->getCurrentPage() + $pagination->getPageShift())
            ]);

            $parserRequest->getCurrentNode()
                ->setUrl($this->mainBoardUrl.'profiles.php?'.http_build_query($urlParams))
                ->setName('Users list', true)
                ->setLabel('Users list');

            $dom = $this->loadDomFromUrl(
                $parserRequest->getCurrentNode()->getUrl()
            );

            $parserRequest->getStatus()
                ->updateProgress(20)
                ->send();

            if ($dom) {
                /** @var HtmlNode $avatar */
                /** @var HtmlNode $anchor */
                /** @var HtmlNode $image */
                /** @var HtmlNode $sendmail */
                /** @var HtmlNode $messageAnchor */
                /** @var HtmlNode $subscribers */
                /** @var HtmlNode $paginationTag */
                $avatars = $dom->getElementsByClass('avatar');
                $paginationTag = $dom->getElementsByTag('font')[0];

                $parserRequest->getStatus()
                    ->startSteppedProgress('load_owners_list', count($avatars), 20, 90);

                // extract gallery data;
                foreach ($avatars as $avatar) {
                    $anchor = $avatar->find('a.gal_title');
                    $subscribers = $avatar->find('.subscribers');
                    $sendmail = $avatar->find('.sendmail');
                    $messageAnchor = $sendmail->find('a');
                    $image = $avatar->find('img')[0];

                    if (!$anchor)
                        continue;

                    $ratio = trim(str_replace(['&lt;', '&gt;', 'fans'], ['', '', ''], $subscribers->text()));
                    $messageUrl = $messageAnchor->getAttribute('href');
                    $userId = (substr($messageUrl, (strpos($messageUrl, '%3fuid%3d') + 9)));
                    $username = str_replace('\'s profile', '', $image->getAttribute('alt'));

                    $parserRequest->addParsedNode((new ParsedNode(ParserType::Imagefap, NodeLevel::BoardsList))
                        ->setName($username)
                        ->setIdentifier($userId)
                        ->setUrl($this->mainBoardUrl.substr($anchor->getAttribute('href'), 1))
                        ->setRating($ratio)
                        ->addThumbnail($image->getAttribute('src'))
                    );

                    $parserRequest->getStatus()->executeSteppedProgressStep('load_owners_list');
                }

                // extract pagination data
                if ($paginationTag) {
                    $currentPage = (int)trim($paginationTag->find('b')[0]->text());
                    $parserRequest->getPagination()->setNumericPagination($currentPage, 10, -1);
                }

                $parserRequest->getStatus()->endSteppedProgress('load_owners_list');
                $this->setParserCache($parserRequest, 60);
            }
        }

        return $parserRequest;
    }

    /**
     * Loading user boards
     *
     * @param ParserRequest $parserRequest
     * @return array
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function getBoardsListData(ParserRequest $parserRequest) : ParserRequest
    {
        $parserRequest->getCurrentNode()->setAllowCategory(true)->setAllowTags(true);

        $cachedRequest = $this->getParserCache($parserRequest);

        if ($cachedRequest) {
            $parserRequest->setParsedNodes($cachedRequest->getParsedNodes())
                ->setPagination($cachedRequest->getPagination())
                ->getStatus()
                ->updateProgress(50, 'Loading from cache...')
                ->send();
        } else {
            $currentName = $parserRequest->currentNode->getName();

            if (empty($currentName)) {
                $urlArray = explode('/', $parserRequest->currentNode->getUrl());
                $currentName = end($urlArray);
                $parserRequest->currentNode->setName($currentName);
            }

            $favoritesUrl = $this->mainBoardUrl.'showfavorites.php?userid='.$parserRequest->currentNode->getIdentifier();
            $galleriesUrl = $this->mainBoardUrl.'profile/'.$parserRequest->currentNode->getName().'/galleries';

            $parserRequest->pagination->reset();
            $parserRequest->currentNode
                ->setName($currentName, true)
                ->setLabel($currentName);

            $htmlArray = [
                'GALLERY' => $this->loadHtmlFromUrl($galleriesUrl),
                'FAVORITES' => $this->loadHtmlFromUrl($favoritesUrl)
            ];

            $parserRequest->getStatus()
                ->updateProgress(50)
                ->send();

            foreach ($htmlArray as $section => $html) {
                $dom = new \DOMDocument('1.0', 'UTF-8');
                @$dom->loadHTML($html);

                /** @var \DOMElement $td */
                foreach ($dom->getElementsByTagName('td') as $td) {
                    if (in_array($td->getAttribute('class'), ['blk_favorites', 'blk_galleries'])) {
                        if ($td->hasAttribute('valign') || $td->hasAttribute('align'))
                            continue;

                        /** @var \DOMElement $table */
                        foreach ($td->getElementsByTagName('table') as $table) {
                            if (!isset($table->getElementsByTagName('table')[0])) {
                                /** @var \DOMElement $anchor */
                                /** @var \DOMElement $thumb */
                                $anchor = $table->getElementsByTagName('a')[0];
                                $thumb = $table->getElementsByTagName('img')[0];

                                if (!$anchor->hasAttribute('onclick')) {
                                    $url = $anchor->getAttribute('href');
                                    $paramsUrl = parse_url($url);
                                    parse_str($paramsUrl['query'], $paramsArray);
                                    $identifier = ((int)$paramsArray['userid'] + (int)$paramsArray['folderid']);

                                    $parserRequest->parsedNodes[] = (new ParsedNode(ParserType::Imagefap, NodeLevel::Board))
                                        ->setName($anchor->textContent.' ('.$section.')')
                                        ->setUrl($anchor->getAttribute('href'))
                                        ->setIdentifier($identifier)
                                        ->addThumbnail($thumb->getAttribute('src'))
                                    ;
                                }
                            }
                        }
                    }
                }
            }

            $parserRequest->getStatus()
                ->updateProgress(90)
                ->send();

            $this->setParserCache($parserRequest, 72000);
        }

        return $parserRequest;
    }

    /**
     * Loading user's board galleries
     *
     * @param ParserRequest $parserRequest
     * @return ParserRequest
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function getBoardData(ParserRequest $parserRequest) : ParserRequest
    {
        $parserRequest->getCurrentNode()->setAllowCategory(true)->setAllowTags(true);

        $cachedRequest = $this->getParserCache($parserRequest);

        if ($cachedRequest) {
            $parserRequest->setParsedNodes($cachedRequest->getParsedNodes())
                ->setFiles($cachedRequest->getFiles())
                ->setPagination($cachedRequest->getPagination())
                ->getStatus()
                ->updateProgress(50, 'Loading from cache...')
                ->send();
        } else {
            $boardUrl = $parserRequest->getCurrentNode()->getUrl();
            $pagination = $parserRequest->getPagination();

            if ($pagination->getActive())
                $boardUrl .= '&page='.($pagination->getCurrentPage() + $pagination->getPageShift());

            $html = $this->loadHtmlFromUrl($boardUrl);
            $dom = new \DOMDocument('1.0', 'UTF-8');
            @$dom->loadHTML($html);

            $parserRequest->getStatus()
                ->updateProgress(50)
                ->send();

            $parserRequest->parsedNodes = [];
            $parserRequest->files = [];

            /** @var \DOMElement $tr */
            /** @var \DOMElement $td */
            $tableRows = $dom->getElementsByTagName('tr');
            $tableCells = $dom->getElementsByTagName('td');
            $paginationTag = null;

            /** @var \DOMElement $font */
            foreach ($dom->getElementsByTagName('font') as $font) {
                if ($font->getAttribute('class') === 'blk_favorites') {
                    $paginationTag = $font;

                    // extract pagination data
                    if ($paginationTag) {
                        /** @var \DOMElement $b */
                        $b = $paginationTag->getElementsByTagName('b')[0];
                        $currentPage = ($b) ? (int)trim($b->textContent) : 1;
                        $totalPages = 1;

                        /** @var \DOMElement $anchor */
                        foreach ($paginationTag->getElementsByTagName('a') as $anchor) {
                            if (is_numeric(trim($anchor->nodeValue)) && (int)trim($anchor->nodeValue) > $totalPages) {
                                $totalPages = (int)trim($anchor->nodeValue);
                            }
                        }

                        $parserRequest->getPagination()->setNumericPagination($currentPage, $totalPages, -1);
                    }

                    break;
                }
            }

            if (!$paginationTag)
                $parserRequest->getPagination()->disable();

            if ($tableRows && $tableCells) {
                $mode = null;

                foreach ($tableRows as $trKey => $tr) {
                    if ($tr->hasAttribute('id') && substr($tr->getAttribute('id'), 0, 4) === 'gid-') {
                        $mode = 'board';
                        break;
                    }
                }

                foreach ($tableCells as $tdKey => $td) {
                    if ($td->hasAttribute('id') && substr($td->getAttribute('id'), 0, 4) === 'img-') {
                        $mode = 'gallery';
                        break;
                    }
                }

                if ($mode === 'board') {
                    foreach ($tableRows as $trKey => $tr) {
                        $trId = $tr->getAttribute('id');

                        if (substr($trId, 0, 4) === 'gid-') {
                            /** @var \DOMElement $thumbnailsTr */
                            $thumbnailsTr = $tableRows[($trKey + 1)];
                            /** @var \DOMElement $anchor */
                            $anchor = $tr->getElementsByTagName('a')[0];
                            /** @var \DOMElement $thumbnails */
                            $thumbnails = $thumbnailsTr->getElementsByTagName('img');
                            /** @var \DOMElement[] $cells */
                            $cells = $tr->getElementsByTagName('td');

                            $url = $anchor->getAttribute('href');
                            $galleryName = trim($anchor->textContent);

                            if (strpos($url, '?gid=')) { // NON-PRETTY url
                                $paramsUrl = parse_url($url);
                                parse_str($paramsUrl['query'], $paramsArray);
                                $galleryId = $paramsArray['gid'];
                            } else { // pretty URL
                                $parsedGalleryUrl = parse_url($url);
                                $pathArray = explode('/', $parsedGalleryUrl['path']);
                                $galleryId = end($pathArray);
                            }

                            $galleryUrl = 'https://www.imagefap.com/pictures/'.$galleryId.'/';
                            $galleryUrl .= urlencode(str_replace(' ', '-', $galleryName));

                            $gallery = (new ParsedNode(ParserType::Imagefap, NodeLevel::Gallery))
                                ->setName(StringHelper::clearString($cells[0]->textContent))
                                ->setUrl($galleryUrl)
                                ->setImagesNo(StringHelper::clearString($cells[1]->textContent))
                                ->setIdentifier($galleryId)
                            ;

                            /** @var \DOMElement $thumbnail */
                            foreach ($thumbnails as $thumbnail) {
                                $gallery->addThumbnail($thumbnail->getAttribute('src'));
                            }

                            $parserRequest->parsedNodes[] = $gallery;
                        }
                    }
                } elseif ($mode === 'gallery') {
                    foreach ($tableCells as $tdKey => $td) {
                        if ($td->hasAttribute('id') && substr($td->getAttribute('id'), 0, 4) === 'img-') {
                            /** @var \DOMElement $thumbnail */
                            $thumbnail = $td->getElementsByTagName('img')[0];
                            $anchor = $td->getElementsByTagName('a')[0];

                            $image = (new ParsedFile(ParserType::Imagefap))
                                ->setThumbnail($thumbnail->getAttribute('href'))
                                ->setExtension(FilesHelper::getFileExtension($thumbnail->getAttribute('href')))
                                ->setUrl($anchor->getAttribute('href'))
                                ->setIcon(FileIcon::ImageFap)
                            ;

                            $parserRequest->files[] = $this->modelConverter->convert($image);
                        }
                    }
                }
            }

            $parserRequest->getStatus()
                ->updateProgress(90)
                ->send();

            $this->setParserCache($parserRequest, 3600);
        }

        return $parserRequest;
    }

    /**
     * @param ParserRequest $parserRequest
     * @return ParserRequest
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function getGalleryData(ParserRequest $parserRequest) : ParserRequest
    {
        $cachedRequest = $this->getParserCache($parserRequest);

        if ($cachedRequest) {
            $parserRequest->setFiles($cachedRequest->getFiles())
                ->setPagination($cachedRequest->getPagination())
                ->getStatus()
                ->updateProgress(50, 'Loading from cache...')
                ->send();
        } else {
            $parserRequest->setFiles([]);
            $parserRequest->getPagination()->disable();

            $galleryUrl = $parserRequest->getCurrentNode()->getUrl();
            $imagesNo = $parserRequest->getCurrentNode()->getImagesNo();
            $pagesNo = ceil($imagesNo / 24);

            $parserRequest->getStatus()
                ->updateProgress(20)
                ->send();

            $html = $this->loadHtmlFromUrl($galleryUrl.'?view=0');
            $dom = new \DOMDocument('1.0', 'UTF-8');
            @$dom->loadHTML($html);

            $parserRequest->getStatus()
                ->startSteppedProgress('get_gallery_data', $pagesNo, 20, 90);

            foreach ($dom->getElementsByTagName('a') as $anchor) {
                $anchorHref = $anchor->getAttribute('href');

                if ($userPos = strpos($anchorHref, '?user=')) {
                    $userName = substr($anchorHref, ($userPos + 6));
                    break;
                }
            }

            /** @var \DOMElement $input */
            foreach ($dom->getElementsByTagName('input') as $input) {
                if ($input->hasAttribute('id') && $input->getAttribute('id') === 'gal_gid') {
                    $galleryId = $input->getAttribute('value');
                }
            }

            for ($pageNumber = 0; $pageNumber < $pagesNo; $pageNumber++) {
                $pageGalleryUrl = $galleryUrl.'?view=0&page='.$pageNumber;

                $html = $this->loadHtmlFromUrl($pageGalleryUrl);
                $dom = new \DOMDocument('1.0', 'UTF-8');
                @$dom->loadHTML($html);

                /** @var \DOMElement $td */
                foreach ($dom->getElementsByTagName('td') as $td) {
                    if (is_numeric($td->getAttribute('id'))) { // image TD
                        /** @var \DOMElement $thumbnail */
                        /** @var \DOMElement $anchor */

                        $anchor = $td->getElementsByTagName('a')[0];
                        $thumbnail = $td->getElementsByTagName('img')[0];

                        $imageId = $anchor->getAttribute('name');
                        $fileName = trim($td->getElementsByTagName('font')[1]->textContent);
                        $fileRes = trim($td->getElementsByTagName('font')[2]->textContent);
                        $fileResArray = explode('x', $fileRes);

                        $imagePageUrl = $this->mainBoardUrl.substr($anchor->getAttribute('href'), 1);

                        $parsedFile = (new ParsedFile(ParserType::Imagefap))
                            ->setIdentifier($imageId)
                            ->setUrl($imagePageUrl)
                        ;

                        if (substr($fileName, -3, 3) === '...') { // incomplete file name - can't create final url :/
                            $parserRequest->files[] = $this->getFileData($parsedFile);
                        } else {
                            $fileUrl = 'https://x.imagefapusercontent.com/u/'.$userName.'/'.$galleryId.'/'.$imageId.'/'.$fileName;
                            $headers = $this->getFileHeadersData($fileUrl);

                            $parserRequest->files[] = (new ParsedFile(ParserType::Imagefap, FilesHelper::getFileType($fileUrl, true)))
                                ->setIdentifier($anchor->getAttribute('name'))
                                ->setUrl($imagePageUrl)
                                ->setWidth($fileResArray[0])
                                ->setHeight($fileResArray[1])
                                ->setSize($headers['size'])
                                ->setMimeType($headers['mimeType'])
                                ->setFileUrl($fileUrl)
                                ->setThumbnail($thumbnail->getAttribute('src'))
                                ->setExtension(FilesHelper::getFileExtension($fileUrl))
                                ->setName(FilesHelper::getFileName($fileUrl));
                        }
                    }
                }

                $parserRequest->getStatus()->executeSteppedProgressStep('get_gallery_data');

                if ($this->testGalleryImagesLimitReached(count($parserRequest->getFiles())))
                    break;
            }

            $parserRequest->getStatus()->endSteppedProgress('get_gallery_data');

            $this->setParserCache($parserRequest, 0);
        }

        $parserRequest->getCurrentNode()
            ->setAllowCategory(true)
            ->setAllowTags(true);

        return $parserRequest;
    }

    /**
     * @param ParserRequest $parserRequest
     * @return ParserRequest
     * @throws \ReflectionException
     */
    public function getFileData(ParsedFile $parsedFile) : ParsedFile
    {
        $html1 = $this->loadHtmlFromUrl($parsedFile->getUrl());
        $html2 = substr($html1, strpos($html1, '<script type="application/ld+json">') + 35);
        $html3 = substr($html2, 0, strpos($html2, '</script>'));
        $json = json_decode(trim($html3), true);

        if ($json) { // easy extract data from json
            $headers = $this->getFileHeadersData($json['contentUrl']);

            return $parsedFile
                ->setIdentifier(FilesHelper::getFileName($json['contentUrl']))
                ->setFileUrl(UrlHelper::fixUrl($json['contentUrl'], false)) // don't cut off query string, contains security token :(
                ->setName(FilesHelper::getFileName($json['contentUrl']))
                ->setExtension(FilesHelper::getFileExtension($json['contentUrl']))
                ->setWidth((int)$json['width'])
                ->setHeight((int)$json['height'])
                ->setThumbnail($json['thumbnail'])
                ->setSize($headers['size'])
                ->setMimeType(FilesHelper::getFileMimeType($json['contentUrl'], true))
                ->setType($json['@type'] === 'ImageObject' ? FileType::Image : FileType::Video)
            ;
        }

        return $parsedFile;
    }

    /**
     * @param ParsedFile $parsedFile
     * @return ParsedFile
     * @throws \ReflectionException
     */
    public function getFilePreview(ParsedFile $parsedFile) : ParsedFile
    {
        $this->clearFileCache();

        if (empty($parsedFile->getFileUrl()) || empty($parsedFile->getName()) || empty($parsedFile->getExtension())) {
            $this->getFileData($parsedFile);
        }

        $previewFilePath = $this->previewTempDir.$parsedFile->getFullFilename();
        $previewWebPath = $this->previewTempFolder.$parsedFile->getFullFilename();

        $parsedFile->setLocalUrl($previewWebPath);
        $parsedFile->setPreviewFilePath($previewFilePath);

        $this->downloadFile($parsedFile->getFileUrl(), $previewFilePath, function($resource, $downloadSize, $downloaded, $uploadSize, $uploaded) use ($parsedFile) {
            if ($downloadSize > 0) {
                $redis = (new RedisFactory())->initializeConnection();
                $redis->set($parsedFile->getRedisPreviewKey(), round(($downloaded / $downloadSize) * 100));
                $redis->expire($parsedFile->getRedisPreviewKey(), 10);
            }
        });

        return $parsedFile;
    }

    /**
     * Extracts profile name from URL
     *
     * @param \DOMDocument $dom
     * @return string
     */
    private function extractUsernameFromGallery(\DOMDocument $dom) : string
    {
        /** @var \DOMElement $anchor */
        foreach ($dom->getElementsByTagName('a') as $anchor) {
            if ($anchor->nodeValue == 'Close gallery') {
                $profileUrl = $anchor->getAttribute('href');
                $profileUrlArray = explode('/', $profileUrl);

                foreach ($profileUrlArray as $key => $element) {
                    if ($element == 'profile') {
                        return $profileUrlArray[($key+1)];
                    }
                }
            }
        }

        return '';
    }
}