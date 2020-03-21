<?php

namespace App\Parser;

use App\Entity\Parser\File;
use App\Entity\User;
use App\Enum\FileType;
use App\Enum\NodeLevel;
use App\Enum\ParserType;
use App\Factory\RedisFactory;
use App\Model\Pagination;
use App\Model\PaginationSelector;
use App\Model\ParsedFile;
use App\Model\ParsedNode;
use App\Model\ParserRequest;
use App\Model\SettingsModel;
use App\Parser\Base\AbstractParser;
use App\Parser\Base\ParserInterface;
use App\Parser\FileService\GfycatParser;
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

    /** @var array */
    private $selectorsData = [
        'hot' => [
            'label' => 'Hot',
            'childrens' => null
        ],
        'new' => [
            'label' => 'New',
            'childrens' => null
        ],
        'random' => [
            'label' => 'Random',
            'childrens' => null
        ],
        'rising' => [
            'label' => 'Rising',
            'childrens' => null
        ],
        'top' => [
            'label' => 'Top',
            'childrens' => [
                'hour' => [
                    'label' => 'Last hour',
                    'childrens' => null
                ],
                'day' => [
                    'label' => 'Today',
                    'childrens' => null
                ],
                'week' => [
                    'label' => 'This week',
                    'childrens' => null
                ],
                'month' => [
                    'label' => 'This month',
                    'childrens' => null
                ],
                'year' => [
                    'label' => 'This year',
                    'childrens' => null
                ],
                'all' => [
                    'label' => 'All Time',
                    'childrens' => null
                ]
            ]
        ]
    ];

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
                            ->setName($subreddit->data->display_name_prefixed)
                            ->setDescription(trim($subreddit->data->title))
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

        $parserRequest->getCurrentNode()
            ->setAllowCategory(true)
            ->setAllowTags(true);

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
            ->getPagination()
            ->loadMorePagination();

        $parserRequest->getCurrentNode()
            ->setAllowCategory(true)
            ->setAllowTags(true);

        $this->preparePaginationSelectors($parserRequest);

        $pagination = $parserRequest->getPagination();

        $parserRequest->getStatus()
            ->startSteppedProgress('reddit_parser', $pagination->getCurrentPackage(), 20, 90);

        for ($package = $pagination->getMinPackage(); $package <= $pagination->getCurrentPackage(); $package++) {
            $subreddit = $this->redditApi->getSubreddit($parserRequest);

            if ($subreddit) {
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
                }

                $parserRequest->tokens->after = $subreddit->data->after;
                $parserRequest->tokens->before = $subreddit->data->before;

                $this->cache->set('listing.'.$this->getSubredditName($parserRequest), [
                    'before' => $subreddit->data->before,
                    'after' => $subreddit->data->after
                ]);
            }

            $parserRequest->getStatus()->executeSteppedProgressStep('reddit_parser');
        }

        $parserRequest->getStatus()->endSteppedProgress('reddit_parser');

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

            if ($child->domain === 'gfycat.com') {
                $fileType = FileType::Video;
                $mimeType = 'video/mp4';
            } else {
                $fileType = FileType::Image;
                $mimeType = FilesHelper::getFileMimeType($clearFileUrl, true);
            }

            $parsedFile = (new ParsedFile(ParserType::Reddit, $fileType))
                ->setName(FilesHelper::getFileName($clearFileUrl))
                ->setTitle($child->title)
                ->setExtension(FilesHelper::getFileExtension($clearFileUrl))
                ->setIdentifier($image->id)
                ->setWidth($image->source->width)
                ->setHeight($image->source->height)
                ->setMimeType($mimeType)
                ->setSize(0)
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

    /**
     * @param ParsedFile $parsedFile
     * @return ParsedFile
     * @throws \PHPHtmlParser\Exceptions\ChildNotFoundException
     * @throws \PHPHtmlParser\Exceptions\CircularException
     * @throws \PHPHtmlParser\Exceptions\CurlException
     * @throws \PHPHtmlParser\Exceptions\NotLoadedException
     * @throws \PHPHtmlParser\Exceptions\StrictException
     */
    public function getFileData(ParsedFile &$parsedFile) : ParsedFile
    {
        if ($parsedFile->getType() === FileType::Video) {
            if (GfycatParser::isGfycat($parsedFile))
                GfycatParser::completeFileData($parsedFile);

            $fileHeader = $this->getFileHeadersData(
                $parsedFile->getFileUrl()
            );
        } else {
            $fileHeader = $this->getFileHeadersData(
                $parsedFile->getUrl()
            );
        }

        $parsedFile->setSize($fileHeader['size']);
        $parsedFile->setMimeType($fileHeader['mimeType']);

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
        $this->clearFileCache();

        if (GfycatParser::isGfycat($parsedFile))
            GfycatParser::completeFileData($parsedFile);

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
                $subfolder = DIRECTORY_SEPARATOR.FilesHelper::createFolderNameFromString(
                    str_replace('r_', '', $parentNode->getIdentifier())
                );
            }
        }

        return $subfolder;
    }

    private function preparePaginationSelectors(ParserRequest &$parserRequest)
    {
        $pagination = $parserRequest->getPagination();

        if (!$pagination->getSelectors() || $pagination->getSelectorsCount() === 0) {
            $parserRequest->setPagination(
                $pagination->setSelectors(
                    $this->createPaginationSelectors($this->selectorsData)
                )
            );
        }

        return $parserRequest;
    }

    private function createPaginationSelectors(array $selectorsData = []): array
    {
        $selectors = [];
        $counter = 0;

        foreach ($selectorsData as $selectorSymbol => $selectorData) {
            $paginationSelector = new PaginationSelector();
            $paginationSelector->setValue($selectorSymbol);
            $paginationSelector->setLabel($selectorData['label'] ?? null);

            if ($counter === 0)
                $paginationSelector->setIsActive(true);

            if (array_key_exists('childrens', $selectorData) && $selectorData['childrens']) {
                $childrens = $this->createPaginationSelectors($selectorData['childrens']);

                if ($counter === 0) {
                    $firstKey = key($childrens);
                    $childrens[$firstKey]->setIsActive(true);
                }

                $paginationSelector->setChildrens($childrens);
            }

            $selectors[] = $paginationSelector;
            $counter++;
        }

        return $selectors;
    }
}

