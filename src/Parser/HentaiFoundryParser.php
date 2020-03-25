<?php

namespace App\Parser;

use App\Entity\Parser\File;
use App\Enum\FileType;
use App\Enum\NodeLevel;
use App\Enum\ParserType;
use App\Factory\RedisFactory;
use App\Model\Interfaces\StatusInterface;
use App\Model\ParsedFile;
use App\Model\ParsedNode;
use App\Model\ParserRequest;
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
     * @param ParserRequest $parserRequest
     * @return ParserRequest
     */
    public function getOwnersList(ParserRequest &$parserRequest): ParserRequest
    {
        // NOTHING TO DO HERE - HF HAVEN'T 'OWNERS', USERS ARE STORED IN BOARDS;
        return $parserRequest;
    }

    /**
     * @param ParserRequest $parserRequest
     * @return ParserRequest
     */
    public function getBoardsListData(ParserRequest &$parserRequest) : ParserRequest
    {
        // NOTHING TO DO IN THIS PARSER - HF HAVEN'T BOARDS LIST;

        return $parserRequest;
    }

    /**
     * Parsing all page with users by letter;
     *
     * @param ParserRequest $parserRequest
     * @return ParserRequest
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function getBoardData(ParserRequest &$parserRequest) : ParserRequest
    {
        if (!$this->getParserCache($parserRequest)) {
            $this->login($parserRequest);

            $page = 1;
            $letter = $parserRequest->pagination->currentLetter;
            $parserRequest->pagination->letterPagination($letter);

            $url = $this->mainBoardUrl.'users/byletter/'.$letter.'/page/'.$page;
            $dom = $this->loadDomFromUrl($url);

            $boardName = 'Users list';

            $parserRequest->parsedNodes = $this->parseGalleriesPageData($dom); // parsing first page;
            $parserRequest->currentNode
                ->setName($boardName, true)
                ->setLabel($boardName);

            $parserRequest->getStatus()
                ->updateProgress(20)
                ->send();

            if ($dom->find('.last')) {
                $lastHref = $dom->find('.last')->find('a')->getAttribute('href');
                $lastHrefArray = explode('/', $lastHref);
                $limit = (int)end($lastHrefArray);

                $parserRequest->getStatus()
                    ->startSteppedProgress('get_board_data', $limit, 20, 90);

                if ($limit > 1) {
                    for ($pageNo = 2; $pageNo <= $limit; $pageNo++) {
                        $this->curlRequest->addRequestFromUrl($this->mainBoardUrl.'users/byletter/'.$letter.'/page/'.$pageNo);

                        if ($pageNo % 20 === 0) {
                            $results = $this->curlRequest->executeRequests();

                            foreach ($results as $html) {
                                $domResult = $this->loadDomFromHtml($html);
                                $parserRequest->parsedNodes = array_merge(
                                    $parserRequest->parsedNodes,
                                    $this->parseGalleriesPageData($domResult)
                                );
                            }
                        }

                        $parserRequest->getStatus()->executeSteppedProgressStep('get_board_data');
                    }
                }

                $parserRequest->getStatus()->endSteppedProgress('get_board_data');

                $this->setParserCache($parserRequest, 0);
            }
        }

        return $parserRequest;
    }

    /**
     * @param Dom $dom
     * @return ParsedFile[]
     * @throws \PHPHtmlParser\Exceptions\ChildNotFoundException
     * @throws \PHPHtmlParser\Exceptions\NotLoadedException
     */
    private function parseFilesPageData(Dom $dom): array
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
            $fileUrlStart = substr($fileUrl, 0, 4);

            if (strpos($thumbnailUrl, 'symbolFlash.jpg')) // flash animation? NO, THX!!!
                continue;

            if ($fileUrlStart !== 'http' && $fileUrlStart === '//pi')
                $fileUrl = 'https:'.$fileUrl;

            $files[] = (new ParsedFile(ParserType::HentaiFoundry, FilesHelper::getFileType($fileUrl)))
                ->setUrl($fileUrl)
                ->setThumbnail(substr($thumbnailUrl, 0, strlen($thumbnailUrl) - 1));
        }

        return $files;
    }

    /**
     * Get gallery files;
     *
     * @param ParserRequest $parserRequest
     * @return ParserRequest
     * @throws \Exception
     */
    public function getGalleryData(ParserRequest &$parserRequest) : ParserRequest
    {
        if (!$this->getParserCache($parserRequest)) {
            $parserRequest->files = [];
            $parserRequest->pagination->disable();

            $options = array_merge([
                'dateTo' => null,
                'direction' => 'DESC',
            ], $parserRequest->sorting);

            $this->login($parserRequest);

            $page = 1;

            $galleryUrl = $this->mainBoardUrl.$parserRequest->currentNode->getUrl().'/page/'.$page;
            $dom = $this->loadDomFromUrl($galleryUrl);
            $galleryName = $dom->find('title')[0]->text();
            $galleryName = substr($galleryName, 0, strpos($galleryName, "'s Profile"));

            $parserRequest->getCurrentNode()
                ->setName($galleryName, true)
                ->setLabel($galleryName);

            $parserRequest->getStatus()
                ->updateProgress(20)
                ->send();

            $parsedFiles = $this->parseFilesPageData($dom);

            if (count($dom->find('.last')) > 0) {
                $lastDiv = $dom->find('.last');

                if ($lastDiv) {
                    $lastHref = $lastDiv->find('a')->getAttribute('href');
                    $lastHrefArray = explode('/', $lastHref);
                    $limit = (int)end($lastHrefArray);

                    for ($page = 2; $page <= $limit; $page++) {
                        $galleryUrl = $this->mainBoardUrl.$parserRequest->currentNode->url.'/page/'.$page;
                        $pageDom = $this->loadDomFromUrl($galleryUrl);
                        $parsedFiles = array_merge($parsedFiles, $this->parseFilesPageData($pageDom));
                    }
                }
            }

            if ($parsedFiles) {
                $parserRequest->getStatus()
                    ->startSteppedProgress('get_gallery_data', count($parsedFiles), 20, 90);

                if (strtoupper($options['direction']) == 'ASC') { // reverse array order;
                    $parsedFiles = array_reverse($parsedFiles, false);
                }

                foreach ($parsedFiles as $parsedFileIndex => $parsedFile) {
                    $this->curlRequest->addRequestFromUrl($this->mainBoardUrl.$parsedFile->getUrl(), [
                        'customKey' => $parsedFileIndex
                    ]);

                    $convertedFileIndex = 0;

                    if (($parsedFileIndex + 1) % 10 === 0 || ($parsedFileIndex + 1) === count($parsedFiles)) {
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
                                $imageSrc = 'https:'.str_replace('this.src=\'', '', $onclick);
                                $imageSrc = substr(0, strpos($imageSrc, '\';'));
                            } else {
                                $imageSrc = 'https:'.$image->getAttribute('src');
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
                                    continue;
                                }
                            }

                            $headersData = $this->getFileHeadersData($imageSrc);

                            $parsedFile = $parsedFiles[$resultKey];

                            $parserRequest->addFile(
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
                            );

                            $convertedFileIndex++;
                        }
                    }

                    $parserRequest->getStatus()->executeSteppedProgressStep('get_gallery_data');
                }

                $this->setParserCache($parserRequest, 3600);
            }
        }

        $parserRequest->getCurrentNode()
            ->setAllowCategory(true)
            ->setAllowTags(true);

        return $parserRequest;
    }

    /**
     * @param string $fileHref
     * @return array
     * @throws \Exception
     */
    public function getFileData(ParsedFile &$parsedFile) : ParsedFile
    {
        $this->login($parsedFile);

        $url = $this->mainBoardUrl.$parsedFile->url;
        $dom = $this->loadDomFromUrl($url);

        /** @var HtmlNode $image */
        $image = $dom->find('img.center')[0];
        /** @var HtmlNode $time */
        $time = $dom->find('time')[0];

        $imageUrl = 'https:'.$image->getAttribute('src');
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
     * @return ParserRequest
     * @throws \Exception
     */
    public function getFilePreview(ParsedFile &$parsedFile) : ParsedFile
    {
        $this->login($parsedFile);
        $this->clearFileCache();

        if (!$parsedFile->getFileUrl() || !$parsedFile->getName() || $parsedFile->getExtension()) {
            $this->getFileData($parsedFile);
        }

        $previewFilePath = $this->previewTempDir.$parsedFile->getFullFilename();
        $previewWebPath = $this->previewTempFolder.$parsedFile->getFullFilename();

        $parsedFile->setLocalUrl($previewWebPath);

        if (!file_exists($previewFilePath)) {
            $this->downloadFile($parsedFile->getFileUrl(), $previewFilePath, function($resource, $downloadSize, $downloaded, $uploadSize, $uploaded) use ($parsedFile) {
                if ($downloadSize > 0) {
                    $redis = (new RedisFactory())->initializeConnection();
                    $redis->set($parsedFile->getRedisPreviewKey(), round(($downloaded / $downloadSize) * 100));
                    $redis->expire($parsedFile->getRedisPreviewKey(), 10);
                }
            });
        }
        
        return $parsedFile;
    }

    /**
     * @param File $file
     * @return string|null
     */
    public function determineFileSubfolder(File $file): ?string
    {
        $subfolder = '';

        if ($user = $file->getParentNode()) {
            $subfolder = DIRECTORY_SEPARATOR.$user->getName();
        }

        return $subfolder;
    }

    /**
     * Parsing one gallery page data
     *
     * @param Dom $dom
     * @return array
     * @throws \PHPHtmlParser\Exceptions\ChildNotFoundException
     * @throws \PHPHtmlParser\Exceptions\NotLoadedException
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

            $nodeModel = (new ParsedNode(ParserType::HentaiFoundry, NodeLevel::Gallery))
                ->setName($profileAnchor->text())
                ->setIdentifier($profileAnchor->text())
                ->setUrl(substr($countAnchor->getAttribute('href'), 1))
                ->setImagesNo($imagesNo)
            ;

            if (strpos($thumbnailAlt, 'featured picture')) {
                $nodeModel->addThumbnail($thumbnail->getAttribute('src'));
            }

            $galleries[] = $nodeModel;
        }

        return $galleries;
    }

    /**
     * @param StatusInterface $parserRequest
     * @throws \Exception
     * @throws \PHPHtmlParser\Exceptions\ChildNotFoundException
     * @throws \PHPHtmlParser\Exceptions\CircularException
     * @throws \PHPHtmlParser\Exceptions\CurlException
     * @throws \PHPHtmlParser\Exceptions\NotLoadedException
     * @throws \PHPHtmlParser\Exceptions\StrictException
     */
    private function login(StatusInterface &$parserRequest)
    {
        $parserRequest->getStatus()
            ->updateProgress(10)
            ->send();

        if (!$this->isLoggedIn()) {
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
            curl_close($ch);
        }

        $parserRequest->getStatus()
            ->updateProgress(15)
            ->send();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    private function isLoggedIn() : bool
    {
        $cookieTime = (int)$this->cache->get('parser.hentai_foundry.cookie_time');

        if ($cookieTime > 0) {
            $cookieDate = (new \DateTime())->setTimestamp($cookieTime);
            $currentDate = (new \DateTime())->modify('-1 second');

            return ($currentDate <= $cookieDate);
        }

        return false;
    }
}