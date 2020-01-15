<?php

namespace App\Parser;

use App\Enum\FileType;
use App\Enum\NodeLevel;
use App\Enum\ParserType;
use App\Model\ParsedFile;
use App\Model\ParsedNode;
use App\Model\ParserRequestModel;
use App\Parser\Base\AbstractParser;
use App\Parser\Base\ParserInterface;
use App\Utils\FilesHelper;
use PHPHtmlParser\Dom;
use PHPHtmlParser\Dom\HtmlNode;

class HentaiFoundryParser extends AbstractParser implements ParserInterface
{
    protected $parserName = ParserType::HentaiFoundry;

    protected $mainBoardUrl = 'http://www.hentai-foundry.com/';

    protected $mainGalleryUrl = 'https://thumbs.hentai-foundry.com/';

    protected $mainMediaUrl = 'https://pictures.hentai-foundry.com/';

    /**
     * @param ParserRequestModel $parserRequestModel
     * @return ParserRequestModel
     */
    public function getOwnersList(ParserRequestModel &$parserRequestModel): ParserRequestModel
    {
        // NOTHING TO DO HERE - HF HAVEN'T 'OWNERS', USERS ARE STORED IN BOARDS;
        return $parserRequestModel;
    }

    /**
     * @param ParserRequestModel $parserRequestModel
     * @return ParserRequestModel
     */
    public function getBoardsListData(ParserRequestModel &$parserRequestModel) : ParserRequestModel
    {
        // NOTHING TO DO IN THIS PARSER - HF HAVEN'T BOARDS LIST;

        return $parserRequestModel;
    }

    /**
     * Parsing all page with users by letter;
     *
     * @param ParserRequestModel $parserRequestModel
     * @return ParserRequestModel
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function getBoardData(ParserRequestModel &$parserRequestModel) : ParserRequestModel
    {
        if (!$this->getParserCache($parserRequestModel)) {
            $this->login();

            $page = 1;
            $letter = $parserRequestModel->pagination->currentLetter;
            $parserRequestModel->pagination->letterPagination($letter);

            $url = $this->mainBoardUrl.'users/byletter/'.$letter.'/page/'.$page;
            $dom = $this->loadDomFromUrl($url);

            $parserRequestModel->currentNode->url = $url;
            $parserRequestModel->parsedNodes = $this->parseGalleriesPageData($dom); // parsing first page;

            $this->setPageLoaderProgress(20);

            if ($dom->find('.last')) {
                $lastHref = $dom->find('.last')->find('a')->getAttribute('href');
                $lastHrefArray = explode('/', $lastHref);
                $limit = (int)end($lastHrefArray);

                $this->startProgress('get_board_data', $limit, 20, 90);

                if ($limit > 1) {
                    for ($pageNo = 2; $pageNo <= $limit; $pageNo++) {
                        $this->curlRequest->addRequestFromUrl($this->mainBoardUrl.'users/byletter/'.$letter.'/page/'.$pageNo);

                        if ($pageNo % 20 === 0) {
                            $results = $this->curlRequest->executeRequests();

                            foreach ($results as $html) {
                                $domResult = $this->loadDomFromHtml($html);
                                $parserRequestModel->parsedNodes = array_merge(
                                    $parserRequestModel->parsedNodes,
                                    $this->parseGalleriesPageData($domResult)
                                );
                            }
                        }

                        $this->progressStep('get_board_data');
                    }
                }

                $this->setParserCache($parserRequestModel, 0);
                $this->endProgress('get_board_data');
            }
        }

        return $parserRequestModel;
    }

    /**
     * @param Dom $dom
     * @return array
     * @throws \ReflectionException
     */
    private function parseFilesPageData(Dom $dom) : array
    {
        $files = [];

        /** @var HtmlNode $imageDiv */
        foreach ($dom->find('.thumb_square') as $imageDiv) {
            /** @var HtmlNode $imageAnchor */
            /** @var HtmlNode $thumbnailSpan */
            $imageAnchor = $imageDiv->find('a')[0];
            $thumbnailSpan = $imageDiv->find('span')[0];
            $thumbnailStyle = $thumbnailSpan->getAttribute('style');
            $thumbnailUrl = substr($thumbnailStyle, strpos($thumbnailStyle, 'url(') + 4);
            $fileUrl = substr($imageAnchor->getAttribute('href'), 1);

            if (strpos($thumbnailUrl, 'symbolFlash.jpg')) { // flash animation? NO, THX!!!
                continue;
            }

            $files[] = (new ParsedFile(ParserType::HentaiFoundry, FilesHelper::getFileType($fileUrl)))
                ->setUrl($fileUrl)
                ->setThumbnail(substr($thumbnailUrl, 0, strlen($thumbnailUrl) - 1));
        }

        return $files;
    }

    /**
     * Get gallery files;
     *
     * @param ParserRequestModel $parserRequestModel
     * @return array
     * @throws \Exception
     */
    public function getGalleryData(ParserRequestModel &$parserRequestModel) : ParserRequestModel
    {
        if (!$this->getParserCache($parserRequestModel)) {
            $parserRequestModel->files = [];
            $parserRequestModel->pagination->disable();

            $options = array_merge([
                'dateTo' => null,
                'direction' => 'DESC',
            ], $parserRequestModel->sorting);

            $this->login();

            $page = 1;
            $files = [];

            $galleryUrl = $this->mainBoardUrl.$parserRequestModel->currentNode->url.'/page/'.$page;
            $dom = $this->loadDomFromUrl($galleryUrl);

            $this->setPageLoaderProgress(20);

            $rawFiles = $this->parseFilesPageData($dom);

            if (count($dom->find('.last')) > 0) {
                $lastDiv = $dom->find('.last');

                if ($lastDiv) {
                    $lastHref = $lastDiv->find('a')->getAttribute('href');
                    $lastHrefArray = explode('/', $lastHref);
                    $limit = (int)end($lastHrefArray);

                    for ($page = 2; $page <= $limit; $page++) {
                        $galleryUrl = $this->mainBoardUrl.$parserRequestModel->currentNode->url.'/page/'.$page;
                        $pageDom = $this->loadDomFromUrl($galleryUrl);
                        $rawFiles = array_merge($rawFiles, $this->parseFilesPageData($pageDom));
                    }
                }
            }

            if ($rawFiles) {
                $this->startProgress('get_gallery_data', count($rawFiles), 20, 90);

                if (strtoupper($options['direction']) == 'ASC') { // reverse array order;
                    $rawFiles = array_reverse($rawFiles, false);
                }

                foreach ($rawFiles as $rawFileIndex => $rawFile) {
                    $this->curlRequest->addRequestFromUrl($this->mainBoardUrl.$rawFile['url'], [
                        'customKey' => $rawFileIndex
                    ]);

                    $convertedFileIndex = 0;

                    if (($rawFileIndex + 1) % 10 === 0 || ($rawFileIndex + 1) === count($rawFiles)) {
                        $results = $this->curlRequest->executeRequests();

                        foreach ($results as $resultKey => $fileHtml) {
                            /** @var Dom $fileDom */
                            $fileDom = $this->loadDomFromHtml($fileHtml);
                            /** @var HtmlNode $image */
                            $image = $fileDom->find('img.center')[0];
                            /** @var HtmlNode $time */
                            $time = $fileDom->find('time')[0];

                            if ($image->hasAttribute('onclick')) {
                                $onclick = $image->getAttribute('onclick');
                                $imageSrc = 'http:'.str_replace('this.src=\'', '', $onclick);
                                $imageSrc = substr(0, strpos($imageSrc, '\';'));
                            } else {
                                $imageSrc = 'http:'.$image->getAttribute('src');
                            }

                            $imageName = FilesHelper::getFileName($imageSrc);

                            foreach (explode('-', $imageName) as $imageNamePart) {
                                if (is_numeric($imageNamePart)) {
                                    $imageId = $imageNamePart;
                                    break;
                                }
                            }

                            $thumbnailUrl = 'https://thumbs.hentai-foundry.com/thumb.php?pid='.$imageId.'&size=350';

                            $imageUploadedAt = (new \DateTime($time->getAttribute('datetime')));

                            if ($options['dateTo']) {
                                if (strtoupper($options['direction']) === 'DESC' && $imageUploadedAt < $options['dateTo']) {
                                    return $files;
                                }
                            }

                            $headersData = $this->getFileHeadersData($imageSrc);

                            $parsedFile = new ParsedFile();

                            $this->modelConverter->setData($rawFiles[$convertedFileIndex], $parsedFile);

                            $parsedFile->setUploadedAt($imageUploadedAt)
                                ->setFileUrl($imageSrc)
                                ->setThumbnail($thumbnailUrl)
                                ->setName(FilesHelper::getFileName($imageSrc))
                                ->setExtension(FilesHelper::getFileExtension($imageSrc))
                                ->setWidth((int)$image->getAttribute('width'))
                                ->setHeight((int)$image->getAttribute('height'))
                                ->setSize($headersData['size'])
                                ->setMimeType($headersData['mimeType'])
                                ->setType(FileType::Image)
                            ;

                            $files[$resultKey] = $parsedFile;

                            $convertedFileIndex++;
                        }
                    }


                    $this->progressStep('get_gallery_data');
                }

                $parserRequestModel->files = $files;
                $this->setParserCache($parserRequestModel, 3600);
            }
        }

        return $parserRequestModel;
    }

    /**
     * @param string $fileHref
     * @return array
     * @throws \Exception
     */
    public function getFileData(ParsedFile &$parsedFile) : ParsedFile
    {
        $this->login();

        $url = $this->mainBoardUrl.$parsedFile->url;
        $dom = $this->loadDomFromUrl($url);

        /** @var HtmlNode $image */
        $image = $dom->find('img.center')[0];
        /** @var HtmlNode $time */
        $time = $dom->find('time')[0];

        $imageUrl = $image->getAttribute('src');
        $imageName = FilesHelper::getFileName($imageUrl);
        $imageIdentifier = explode('-', $imageName)[1];

        return $parsedFile->setUrl($imageUrl)
            ->setName($imageName)
            ->setExtension(FilesHelper::getFileExtension($imageUrl))
            ->setIdentifier($imageIdentifier)
            ->setWidth((int)$image->getAttribute('width'))
            ->setHeight((int)$image->getAttribute('height'))
            ->setMimeType(FilesHelper::getFileMimeType($imageUrl, true))
            ->setType(FileType::Image)
            ->setUploadedAt((new \DateTime($time->getAttribute('datetime')))->getTimestamp());
    }

    /**
     * @param ParsedFile $parsedFile
     * @return ParserRequestModel
     * @throws \Exception
     */
    public function getFilePreview(ParsedFile &$parsedFile) : ParsedFile
    {
        $this->login();
        $this->clearCache();

        if (!$parsedFile->getFileUrl() || !$parsedFile->getName() || $parsedFile->getExtension()) {
            $this->getFileData($parsedFile);
        }

        $previewFilePath = $this->previewTempDir.$parsedFile->getFullFilename();
        $previewWebPath = $this->previewTempFolder.$parsedFile->getFullFilename();

        $parsedFile->setLocalUrl($previewWebPath);

        $this->downloadFile($parsedFile->getFileUrl(), $previewFilePath);

        return $parsedFile;
    }

    /**
     * Parsing one gallery page data
     *
     * @param Dom $dom
     * @return array
     * @throws \ReflectionException
     */
    private function parseGalleriesPageData(Dom $dom) : array
    {
        $galleries = [];

        /** @var HtmlNode[] $userRowDivs */
        $userRowDivs = $dom->find('div.userRow');

        foreach ($userRowDivs as $userRowDiv) {
            /** @var HtmlNode $thumbnail */
            $thumbnail = $userRowDiv->find('img')[0];
            $thumbnailAlt = $thumbnail->getAttribute('alt');

            /** @var HtmlNode $profileAnchor */
            $profileAnchor = $userRowDiv->find('.username')->find('a');
            /** @var HtmlNode $countAnchor */
            $countAnchor = $userRowDiv->find('.count')->find('a');
            $countText = $countAnchor->text();
            $imagesNo = (int)str_replace(' pictures', '', $countText);

            if ($imagesNo < 10) {
                continue;
            }

            $nodeModel = (new ParsedNode(ParserType::HentaiFoundry, NodeLevel::Board))
                ->setName($profileAnchor->text())
                ->setIdentifier($profileAnchor->text())
                ->setUrl(substr($countAnchor->getAttribute('href'), 1))
                ->setImagesNo($imagesNo)
                ->setNextLevel(NodeLevel::Gallery)
            ;

            if (strpos($thumbnailAlt, 'featured picture')) {
                $nodeModel->addThumbnail($thumbnail->getAttribute('src'));
            }

            $galleries[] = $this->modelConverter->convert($nodeModel);
        }

        return $galleries;
    }

    private function login()
    {
        $this->setPageLoaderProgress(10);

        if ($this->isLoggedIn()) {
            $this->setPageLoaderProgress(15);
            return;
        }

        $username = "stud3nt";
        $password = "4710bbb";

        $tokenUrl = $this->mainBoardUrl.'?enterAgree=1&size=0';
        $loginUrl = $this->mainBoardUrl.'site/login';

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $tokenUrl);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->curlRequest->getCookieFile());
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->curlRequest->getCookieFile());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        $dom = $this->loadDomFromHTML($response);
        $token = $dom->find('[name=YII_CSRF_TOKEN]')->getAttribute('value');
        $params = [
            'LoginForm[username]' => $username,
            'LoginForm[password]' => $password,
            'LoginForm[rememberMe]' => 1,
            'YII_CSRF_TOKEN' => $token
        ];

        curl_setopt($ch, CURLOPT_URL, $loginUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0)");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->curlRequest->getCookieFile());
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->curlRequest->getCookieFile());
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));

        curl_exec($ch);

        //$this->cache->save('parser.hentai_foundry.cookie_time', time() + 3600);

        curl_close($ch);

        $this->setPageLoaderProgress(15);
    }

    private function isLoggedIn() : bool
    {
        $cookieTime = (int)$this->cache->read('parser.hentai_foundry.cookie_time');

        if ($cookieTime > 0) {
            $cookieDate = (new \DateTime())->setTimestamp($cookieTime);
            $currentDate = (new \DateTime())->modify('-1 second');

            return ($currentDate <= $cookieDate);
        }

        return false;
    }
}