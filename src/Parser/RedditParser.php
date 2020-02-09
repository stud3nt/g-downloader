<?php

namespace App\Parser;

use App\Entity\Parser\File;
use App\Entity\User;
use App\Enum\FileType;
use App\Enum\NodeLevel;
use App\Enum\ParserType;
use App\Factory\RedisFactory;
use App\Model\ParsedFile;
use App\Model\ParsedNode;
use App\Model\ParserRequest;
use App\Model\SettingsModel;
use App\Parser\Base\AbstractParser;
use App\Parser\Base\ParserInterface;
use App\Service\Reddit\RedditApi;
use App\Traits\CurrentUrlTrait;
use App\Utils\FilesHelper;
use PHPHtmlParser\Dom\HtmlNode;

class RedditParser extends AbstractParser implements ParserInterface
{
    use CurrentUrlTrait;

    protected $parserName = ParserType::Reddit;

    /** @var RedditApi */
    protected $redditApi;

    /**
     * RedditParser constructor.
     * @param SettingsModel $settings
     * @param User $user
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Exception
     */
    public function __construct(SettingsModel $settings, User $user)
    {
        parent::__construct($settings, $user);

        $this->redditApi = (new RedditApi())->init($this->settings);
    }

    public function getOwnersList(ParserRequest &$parserRequest): ParserRequest
    {
        // NOTHING TO DO HERE
        return $parserRequest;
    }

    /**
     * Gets subreddits list
     *
     * @param int $page
     * @param array $options
     * @return array
     * @throws \ReflectionException
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Exception
     */
    public function getBoardsListData(ParserRequest &$parserRequest) : ParserRequest
    {
        $parserRequest->clearParsedData();

        if (!$this->getParserCache($parserRequest)) {
            $after = null;
            $nextPage = true;

            $parserRequest->getStatus()
                ->updateProgress(20)
                ->send();

            $parserRequest->parsedNodes = [];
            $parserRequest->currentNode->url = $this->mainBoardUrl;
            $parserRequest->pagination->disable();

            while ($nextPage === true) {
                $subreddits = $this->redditApi->getSubredditsList($after);

                if ($subreddits && $subreddits->data && count($subreddits->data->children) > 0) {
                    foreach ($subreddits->data->children as $subreddit) {
                        $parserRequest->addParsedNode((new ParsedNode(ParserType::Reddit, NodeLevel::Board))
                            ->setName($subreddit->data->title)
                            ->setDescription(trim($subreddit->data->public_description))
                            ->setUrl($subreddit->data->display_name_prefixed)
                            ->setIdentifier($subreddit->data->display_name_prefixed)
                            ->setNoImage(true)
                        );
                    }
                }

                if (!$subreddits->data->after) {
                    $nextPage = false;
                }

                $after = $subreddits->data->after;
            }

            $parserRequest->getStatus()
                ->updateProgress(90)
                ->send();

            $this->setParserCache($parserRequest, 0);
        }

        return $parserRequest;
    }

    /**
     * @param ParserRequest $parserRequest
     * @return array
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function getBoardData(ParserRequest &$parserRequest) : ParserRequest
    {
        $parserRequest->clearParsedData()
            ->pagination->loadMorePagination();

        $parserRequest->getStatus()
            ->updateProgress(5)
            ->send();

        $subreddit = $this->redditApi->getSubreddit($parserRequest);

        $parserRequest->getStatus()
            ->updateProgress(20)
            ->send();

        if ($subreddit) {
            $parserRequest->getStatus()
                ->startSteppedProgress('get_board_data', 100, 20, 90);

            foreach ($subreddit->data->children as $childIndex => $child) {
                if (property_exists($child->data, 'crosspost_parent_list')) { // this is not post, but crosspost :/
                    foreach ($child->data->crosspost_parent_list as $parentChild) {
                        if (property_exists($parentChild, 'preview')) {
                            $childData = $this->processBoardChildData($parentChild);

                            if ($childData) {
                                foreach ($childData as $file) {
                                    $parserRequest->addFile($file);
                                }
                            }
                        }
                    }
                } else {
                    if (property_exists($child->data, 'preview')) {
                        $childData = $this->processBoardChildData($child->data);

                        if ($childData) {
                            foreach ($childData as $file) {
                                $parserRequest->addFile($file);
                            }
                        }
                    }
                }

                $parserRequest->getStatus()->executeSteppedProgressStep('get_board_data');
            }

            $parserRequest->getStatus()
                ->endSteppedProgress('get_board_data');

            $this->cache->set('listing.'.$this->getSubredditName($parserRequest), [
                'before' => $subreddit->data->before,
                'after' => $subreddit->data->after
            ]);

            $parserRequest->tokens->after = $subreddit->data->after;
            $parserRequest->tokens->before = $subreddit->data->before;
        }

        return $parserRequest;
    }

    /**
     * @param $child
     * @return array
     * @throws \ReflectionException
     */
    private function processBoardChildData($child) : array
    {
        $parsedFiles = [];

        foreach ($child->preview->images as $image) {
            $clearFileUrl = strtok($image->source->url, '?');
            $fileHeader = $this->getFileHeadersData($image->source->url);

            if ($child->domain === 'gfycat.com') {
                $fileType = FileType::Video;
                $mimeType = 'video/mp4';
            } else {
                $fileType = FileType::Image;
                $mimeType = $fileHeader['mimeType'];
            }

            $parsedFile = (new ParsedFile(ParserType::Reddit, $fileType))
                ->setName(FilesHelper::getFileName($clearFileUrl))
                ->setTitle($child->title)
                ->setExtension(FilesHelper::getFileExtension($clearFileUrl))
                ->setIdentifier($image->id)
                ->setWidth($image->source->width)
                ->setHeight($image->source->height)
                ->setMimeType($mimeType)
                ->setSize($fileHeader['size'])
            ;

            if ($child->domain === 'gfycat.com') {
                $parsedFile->setUrl($child->url);
            } else {
                $parsedFile->setUrl($image->source->url);
                $parsedFile->setFileUrl($image->source->url);
            }

            foreach ($image->resolutions as $imagePreview) {
                if ($imagePreview->width > 230 || $imagePreview->height > 260) {
                    $parsedFile->setThumbnail($imagePreview->url);
                    break;
                }
            }

            $parsedFiles[] = $parsedFile;
        }

        return $parsedFiles;
    }

    public function getGalleryData(ParserRequest &$parserRequest = null) : ParserRequest
    {
        return $parserRequest;
    }

    public function getFileData(ParsedFile &$parsedFile) : ParsedFile
    {
        return $parsedFile;
    }

    /**
     * @param ParsedFile $parsedFile
     * @return ParsedFile
     * @throws \PHPHtmlParser\Exceptions\ChildNotFoundException
     * @throws \PHPHtmlParser\Exceptions\CircularException
     * @throws \PHPHtmlParser\Exceptions\CurlException
     * @throws \PHPHtmlParser\Exceptions\NotLoadedException
     * @throws \PHPHtmlParser\Exceptions\StrictException
     */
    public function getFilePreview(ParsedFile &$parsedFile) : ParsedFile
    {
        $this->clearCache();

        // gfycat -> extract file url from page
        if (parse_url($parsedFile->getUrl())['host'] === 'gfycat.com') {
            $dom = $this->loadDomFromUrl($parsedFile->getUrl());
            $videos = $dom->getElementsByTag('video');

            /** @var HtmlNode $video */
            /** @var HtmlNode $source */
            foreach ($videos as $video) {
                if ($video->getAttribute('class') === 'video media') {
                    $sources = $video->find('source');

                    foreach ($sources as $source) {
                        $src = $source->getAttribute('src');

                        if (strpos($src, 'giant.gfycat.com') && strpos($src, '.mp4')) {
                            $parsedFile->setFileUrl($src);
                            break;
                        }
                    }
                }
            }
        }

        $previewFilePath = $this->previewTempDir.$parsedFile->getFullFilename();
        $previewWebPath = $this->previewTempFolder.$parsedFile->getFullFilename();

        $parsedFile->setLocalUrl($previewWebPath);

        $this->downloadFile($parsedFile->getFileUrl() ?? $parsedFile->getUrl(), $previewFilePath, function($resource, $downloadSize, $downloaded, $uploadSize, $uploaded) use ($parsedFile) {
            if ($downloadSize > 0) {
                $redis = (new RedisFactory())->initializeConnection();
                $redis->set($parsedFile->getRedisPreviewKey(), round(($downloaded / $downloadSize) * 100));
                $redis->expire($parsedFile->getRedisPreviewKey(), 10);
            }
        });

        return $parsedFile;
    }

    public function getSubredditName(ParserRequest $parserRequest) : string
    {
        $urlArray = explode('/', $parserRequest->currentNode->url);

        if ($urlArray) {
            foreach ($urlArray as $key => $value) {
                if ($value === 'r') {
                    return $urlArray[($key+1)];
                }
            }
        }

        return '';
    }

    public function determineFileSubfolder(File $file): ?string
    {
        $subfolder = '';

        if ($parentNode = $file->getParentNode()) {
            if ($parentNode->getLevel() === NodeLevel::Board) {
                $subfolder = DIRECTORY_SEPARATOR.FilesHelper::createFolderNameFromString($parentNode->getName());
            }
        }

        return $subfolder;
    }
}