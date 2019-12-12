<?php

namespace App\Parser;

use App\Converter\ModelConverter;
use App\Enum\FileType;
use App\Enum\NodeLevel;
use App\Enum\ParserType;
use App\Enum\SettingsGroups;
use App\Manager\SettingsManager;
use App\Model\ParsedFile;
use App\Model\ParsedNode;
use App\Model\ParserRequestModel;
use App\Parser\Base\AbstractParser;
use App\Parser\Base\ParserInterface;
use App\Service\Reddit\RedditApi;
use App\Traits\CurrentUrlTrait;
use App\Utils\FilesHelper;

class RedditParser extends AbstractParser implements ParserInterface
{
    use CurrentUrlTrait;

    protected $parserName = ParserType::Reddit;

    /** @var RedditApi */
    protected $redditApi;

    /**
     * RedditParser constructor.
     *
     * @throws \ReflectionException
     * @throws \Exception
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function __construct(SettingsManager $settingsManager, ModelConverter $modelConverter)
    {
        parent::__construct($settingsManager, $modelConverter);

        $redditSettings = $this->settingsManager->getSettings([
            'group' => SettingsGroups::Reddit
        ], true);

        $this->redditApi = (new RedditApi())->init($redditSettings);
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
    public function getBoardsListData(ParserRequestModel &$parserRequestModel) : ParserRequestModel
    {
        if (!$this->getParserCache($parserRequestModel)) {
            $after = null;
            $nextPage = true;

            $parserRequestModel->parsedNodes = [];
            $parserRequestModel->currentNode->url = $this->mainBoardUrl;
            $parserRequestModel->pagination->disable();

            while ($nextPage === true) {
                $subreddits = $this->redditApi->getSubredditsList($after);

                if ($subreddits && $subreddits->data && count($subreddits->data->children) > 0) {
                    foreach ($subreddits->data->children as $subreddit) {
                        $parserRequestModel->parsedNodes[] = $this->modelConverter->convert(
                            (new ParsedNode(ParserType::Reddit, NodeLevel::BoardsList))
                                ->setName($subreddit->data->title)
                                ->setDescription(trim($subreddit->data->public_description))
                                ->setNextLevel(NodeLevel::Board)
                                ->setUrl($subreddit->data->display_name_prefixed)
                                ->setIdentifier($subreddit->data->display_name_prefixed)
                                ->setNoImage(true)
                        );
                    }
                }

                $this->makeBlindStep(20, 90);

                if (!$subreddits->data->after) {
                    $nextPage = false;
                }

                $after = $subreddits->data->after;
            }

            $this->setParserCache($parserRequestModel, 0);
            $this->setPageLoaderProgress(90);
        }

        return $parserRequestModel;
    }

    /**
     * @param ParserRequestModel $parserRequestModel
     * @return array
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function getBoardData(ParserRequestModel &$parserRequestModel) : ParserRequestModel
    {
        $parserRequestModel->pagination->loadMorePagination();

        if (!$this->getParserCache($parserRequestModel)) {
            $parserRequestModel->parsedNodes = [];
            $subreddit = $this->redditApi->getSubreddit($parserRequestModel);

            $this->setPageLoaderProgress(90);

            if ($subreddit) {
                foreach ($subreddit->data->children as $index => $child) {
                    if (property_exists($child->data, 'crosspost_parent_list')) { // this is not post, but crosspost :/
                        foreach ($child->data->crosspost_parent_list as $parentChild) {
                            if (property_exists($parentChild, 'preview')) {
                                $childData = $this->processBoardChildData($parentChild);

                                if ($childData) {
                                    foreach ($childData as $nodeObject) {
                                        $parserRequestModel->files[] = $this->modelConverter->convert($nodeObject);
                                    }
                                }
                            }
                        }
                    } else {
                        if (property_exists($child->data, 'preview')) {
                            $childData = $this->processBoardChildData($child->data);

                            if ($childData) {
                                foreach ($childData as $nodeObject) {
                                    $parserRequestModel->files[] = $this->modelConverter->convert($nodeObject);
                                }
                            }
                        }
                    }
                }

                $this->cache->set('listing.'.$this->getSubredditName($parserRequestModel), [
                    'before' => $subreddit->data->before,
                    'after' => $subreddit->data->after
                ]);

                $parserRequestModel->tokens->after = $subreddit->data->after;
                $parserRequestModel->tokens->before = $subreddit->data->before;

                $this->setParserCache($parserRequestModel, 90);
            }
        }

        return $parserRequestModel;
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

    public function getGalleryData(ParserRequestModel &$parserRequestModel = null) : ParserRequestModel
    {
        return $parserRequestModel;
    }

    public function getFileData(ParsedFile &$parsedFile) : ParsedFile
    {
        return $parsedFile;
    }

    public function getFilePreview(ParsedFile &$parsedFile) : ParsedFile
    {
        if (!$parsedFile->getFileUrl() || !$parsedFile->getName() || $parsedFile->getExtension()) {
            $this->getFileData($parsedFile);
        }

        $previewFilePath = $this->previewTempDir.$parsedFile->getFullFilename();
        $previewWebPath = $this->previewTempFolder.$parsedFile->getFullFilename();

        $parsedFile->setLocalUrl($previewWebPath);

        $this->downloadFile($parsedFile->getFileUrl(), $previewFilePath);

        return $parsedFile;
    }

    public function getSubredditName(ParserRequestModel $parserRequestModel) : string
    {
        $urlArray = explode('/', $parserRequestModel->currentNode->url);

        if ($urlArray) {
            foreach ($urlArray as $key => $value) {
                if ($value === 'r') {
                    return $urlArray[($key+1)];
                }
            }
        }

        return '';
    }

    private function getThumbnailsFromChild(\stdClass $child, bool $onlyFirst = false)
    {
        $thumbnails = [];

        if ($child && $child->preview->images) {
            foreach ($child->preview->images as $image) {
                $thumbnail = null;

                foreach ($image->resolutions as $imagePreview) {
                    if ($imagePreview->width > 230 || $imagePreview->height > 260) {
                        if ($onlyFirst) {
                            return $imagePreview->url;
                        } else {
                            $thumbnail = $imagePreview->url;
                        }

                        break;
                    }
                }

                // no image found? Get last;
                if ($onlyFirst) {
                    return end($image->resolutions)->url;
                } elseif (empty($thumbnail)) {
                    $thumbnails[] = (empty($thumbnail))
                        ? end($image->resolutions)->url
                        : $thumbnail;
                }
            }
        }

        return $thumbnails;
    }
}