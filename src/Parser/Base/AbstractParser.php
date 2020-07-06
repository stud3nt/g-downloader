<?php

namespace App\Parser\Base;

use App\Converter\ModelConverter;
use App\Entity\Parser\File;
use App\Entity\Parser\Node;
use App\Entity\Parser\NodeSettings;
use App\Entity\User;
use App\Enum\FileType;
use App\Enum\FolderType;
use App\Enum\PaginationMode;
use App\Enum\PrefixSufixType;
use App\Factory\RedisFactory;
use App\Model\ParsedFile;
use App\Model\ParserRequest;
use App\Model\SettingsModel;
use App\Utils\FilesHelper;
use App\Service\{FileCache, CurlRequest};
use App\Utils\AppHelper;
use GuzzleHttp\Client;
use PHPHtmlParser\Dom;
use Symfony\Component\Filesystem\Filesystem;

class AbstractParser implements ParserInterface
{
    // parser name
    protected $parserName = 'parser_name';

    /** @var Client */
    protected $httpClient;

    /** @var Dom */
    protected $domLibrary;

    /** @var CurlRequest */
    protected $curlRequest;

    /** @var FileCache */
    protected $cache;

    /** @var ModelConverter */
    protected $modelConverter;

    /** @var array */
    protected $settings = [];

    /** @var User */
    private $user;

    // local folders
    protected $thumbnailTempDir;
    protected $thumbnailFolder;
    protected $previewTempDir;
    protected $previewTempFolder;

    // url's
    protected $mainBoardUrl;
    protected $mainGalleryUrl;
    protected $mainMediaUrl;

    protected $testOwnersLimit = -1;
    protected $testBoardsLimit = -1;
    protected $testGalleryImagesLimit = -1;
    protected $testGalleriesLimit = -1;

    protected $localThumbnailsLifetime = (60*60*24*7); // initial - 1 week lifetime;

    /**
     * AbstractParser constructor.
     * @param SettingsModel $settings
     * @param User $user
     * @throws \Exception
     */
    public function __construct(SettingsModel $settings, User $user)
    {
        $this->user = $user;

        $this->httpClient = new Client();
        $this->domLibrary = new Dom();
        $this->curlRequest = (new CurlRequest())->init($this->parserName);
        $this->cache = new FileCache($user);

        $this->modelConverter = new ModelConverter();
        $this->settings = $settings;

        $ds = DIRECTORY_SEPARATOR;

        $this->thumbnailFolder = 'temp'.$ds.'parsers'.$ds.$this->parserName.$ds;
        $this->thumbnailTempDir = AppHelper::getPublicDir().$this->thumbnailFolder;

        $this->previewTempFolder = $this->thumbnailFolder.'preview'.$ds;
        $this->previewTempDir = AppHelper::getPublicDir().$this->previewTempFolder;

        if (!file_exists($this->thumbnailTempDir)) {
            mkdir($this->thumbnailTempDir, 0777);
        }

        if (!file_exists($this->previewTempDir)) {
            mkdir($this->previewTempDir, 0777);
        }
    }

    /**
     * @throws \Exception
     */
    public function __destruct()
    {
        $this->clearFileCache();
    }

    /**
     * Dummy implementation for interface function
     *
     * @param ParserRequest $parserRequest
     * @return ParserRequest
     */
    public function getOwnersList(ParserRequest $parserRequest): ParserRequest
    {
        return $parserRequest;
    }

    /**
     * Dummy implementation for interface function
     *
     * @param ParserRequest $parserRequest
     * @return ParserRequest
     */
    public function getBoardsListData(ParserRequest $parserRequest): ParserRequest
    {
        return $parserRequest;
    }

    /**
     * Dummy implementation for interface function
     *
     * @param ParserRequest $parserRequest
     * @return ParserRequest
     */
    public function getBoardData(ParserRequest $parserRequest): ParserRequest
    {
        return $parserRequest;
    }

    /**
     * Dummy implementation for interface function
     *
     * @param ParserRequest $parserRequest
     * @return ParserRequest
     */
    public function getGalleryData(ParserRequest $parserRequest): ParserRequest
    {
        return $parserRequest;
    }

    /**
     * Dummy implementation for interface function
     *
     * @param ParsedFile $parsedFile
     * @return ParsedFile
     */
    public function getFileData(ParsedFile $parsedFile): ParsedFile
    {
        return $parsedFile;
    }

    /**
     * Dummy implementation for interface function
     *
     * @param ParsedFile $parsedFile
     * @return ParsedFile
     */
    public function getFilePreview(ParsedFile $parsedFile): ParsedFile
    {
        return $parsedFile;
    }

    /**
     * Dummy implementation for interface function
     *
     * @param File $file
     * @return string|null
     */
    public function determineFileSubfolder(File $file): ?string
    {
        return null;
    }

    /**
     * Prepares file target directories and sets it to file entity;
     *
     * @param File $file
     * @return File
     */
    public function prepareFileTargetDirectories(File $file): File
    {
        $ds = DIRECTORY_SEPARATOR;
        $fs = new Filesystem();

        $parserSymbol = preg_replace('/[^a-zA-Z0-9\-\_]/', '_', $this->parserName);
        $nodeSettings = $file->getFinalNodeSettings();

        $targetDirectory = $this->settings->getCommonSetting('downloadDirectory');
        $targetDirectory .= $ds.$parserSymbol;
        $targetDirectory .= (($file->getType() === FileType::Video) ? '_webms' : '');

        if ($parserDownloadFolder = $this->settings->getParserSetting($this->parserName, 'downloadFolder')) {
            preg_match_all('/\%[a-zA-Z0-9]{1,}\%/', $parserDownloadFolder, $variables);

            // replacing url phrases with config values
            if ($variables[0] && count($variables[0]) > 0) {
                foreach ($variables[0] as $variable) {
                    $variableName = str_replace('%', '', $variable);
                    $configValue = $this->settings->getParserSetting($this->parserName, $variableName);
                    $parserDownloadFolder = str_replace($parserDownloadFolder, $variable, $configValue);
                }
            }

            $targetDirectory .= $ds.str_replace('%', '', $parserDownloadFolder);
        }

        $targetDirectory .= $this->determineFileSubfolder($file);
        $targetDirectory .= $this->determineSettingsSubfolder($file);

        if (!$fs->exists($targetDirectory)) {
            $fs->mkdir($targetDirectory, 0777);
        }

        $fileName = $this->determineFileName($file);

        if ($file->getType() === FileType::Video)
            $targetFilePath = $targetDirectory.$ds.$fileName.'.'.$file->getExtension();
        else
            $targetFilePath = $targetDirectory.$ds.$fileName.'.jpg';

        // value for standard downloaded file;
        $file->setTempFilePath($this->previewTempDir.$fileName.'.'.$file->getExtension());
        $file->setTargetFilePath($targetFilePath);
        $file->setNodeSettings($nodeSettings);

        return $file;
    }

    public function determineSettingsSubfolder(File $file): ?string
    {
        $subfolder = '';

        if ($settings = $file->getFinalNodeSettings()) {
            if ($settings->getFolderType()) {
                switch ($settings->getFolderType()) {
                    case FolderType::CustomText:
                        $subfolder = $settings->getFolder() ?? null;
                        break;

                    case FolderType::CategoryName:
                        $parentNode = $file->getParentNode();
                        $parentCategory = $parentNode->getCategory();

                        if ($parentCategory) {
                            $rawSubfolder = FilesHelper::folderNameFromString($parentCategory->getName());
                            $subfolder = ($rawSubfolder && strlen($rawSubfolder > 0)) ? $rawSubfolder : null;
                        }
                        break;

                    case FolderType::NodeName:
                        $nodeName = $file->getParentNode()->getName();
                        $rawSubfolder = FilesHelper::folderNameFromString($nodeName);
                        $subfolder = ($rawSubfolder && strlen($rawSubfolder > 0)) ? $rawSubfolder : null;
                        break;

                    case FolderType::NodeSymbol:
                        $nodeSymbol = $file->getParentNode()->getIdentifier();
                        $rawSubfolder = FilesHelper::folderNameFromString($nodeSymbol);
                        $subfolder = ($rawSubfolder && strlen($rawSubfolder > 0)) ? $rawSubfolder : null;
                        break;
                }
            }
        }

        if ($subfolder)
            $subfolder = DIRECTORY_SEPARATOR.$subfolder;

        return $subfolder;
    }

    public function determinePrefixSufixName(File $file, string $type = null, string $value = null): ?string
    {
        switch ($type) {
            case PrefixSufixType::CustomText:
                return (($value) ? FilesHelper::fileNameFromString($value) : null);
                break;

            case PrefixSufixType::NodeSymbol:
                return ($parentNode = $file->getParentNode())
                    ? FilesHelper::fileNameFromString($parentNode->getIdentifier())
                    : null;
                break;

            case PrefixSufixType::NodeName:
                return ($parentNode = $file->getParentNode())
                    ? FilesHelper::fileNameFromString($parentNode->getName())
                    : null;
                break;

            case PrefixSufixType::CategoryName:
                if ($file->getParentNode() && $category = $file->getParentNode()->getCategory())
                    return FilesHelper::fileNameFromString($category->getName());
                else
                    return null;
                break;

            case PrefixSufixType::FileDescription:
                return FilesHelper::fileNameFromString($file->getDescription(), false, 50);
                break;
        }

        return null;
    }

    public function determineFileName(File $file): ?string
    {
        $prefix = null;
        $sufix = null;

        if ($settings = $file->getFinalNodeSettings()) {
            $prefix = $settings->getPrefixType()
                ? $this->determinePrefixSufixName($file, $settings->getPrefixType(), $settings->getPrefix())
                : null;
            $sufix = $settings->getSufixType()
                ? $this->determinePrefixSufixName($file, $settings->getSufixType(), $settings->getSufix())
                : null;
        }

        return $prefix.($prefix ? ' ' : '').$file->getName().($sufix ? ' ' : '').$sufix;
    }

    /**
     * Generates CURL request for file download and defines file locations (temporary and target);
     *
     * @param File $file
     * @return File
     */
    public function generateFileCurlRequest(File $file): File
    {
        $this->prepareFileTargetDirectories($file);

        $curlService = new CurlRequest();
        $redis = (new RedisFactory())->initializeConnection();

        $file->setCurlRequest(
            $curlService->prepareCurlRequest(
                $file->getFileUrl() ?? $file->getUrl(),
                null,
                function($resource, $downloadSize, $downloaded, $uploadSize, $uploaded) use ($file, $redis) {
                    if ($downloadSize > 0) {
                        $redis->set($file->getRedisDownloadKey(), round(($downloaded / $downloadSize) * 100));
                        $redis->expire($file->getRedisDownloadKey(), 500);
                    }
                }
            )
        );

        return $file;
    }

    /**
     * @param Node|null $node
     * @return NodeSettings|null
     */
    public function determineSettings(Node $node = null): ?NodeSettings
    {
        if (!$node)
            return null;

        if ($settings = $node->getSettings())
            return $settings;

        if ($node->getParentNode())
            return $this->determineSettings($node);

        return null;
    }

    /**
     * @param string $url
     * @return Dom
     * @throws \PHPHtmlParser\Exceptions\ChildNotFoundException
     * @throws \PHPHtmlParser\Exceptions\CircularException
     * @throws \PHPHtmlParser\Exceptions\CurlException
     * @throws \PHPHtmlParser\Exceptions\StrictException
     */
    protected function loadDomFromUrl(string $url) : Dom
    {
        return $this->domLibrary->load(
            $this->curlRequest->executeSingleRequest($url)
        );
    }

    /**
     * @param string $html
     * @return Dom
     * @throws \PHPHtmlParser\Exceptions\ChildNotFoundException
     * @throws \PHPHtmlParser\Exceptions\CircularException
     * @throws \PHPHtmlParser\Exceptions\CurlException
     * @throws \PHPHtmlParser\Exceptions\StrictException
     */
    protected function loadDomFromHTML(string $html) : Dom
    {
        return $this->domLibrary->load($html);
    }

    protected function loadHtmlFromUrl(string $url) : string
    {
        return $this->curlRequest->executeSingleRequest($url);
    }

    protected function downloadFile(string $sourceUrl, string $targetUrl, $returnFunction = null)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_URL, $sourceUrl);

        if ($returnFunction) {
            curl_setopt($ch, CURLOPT_NOPROGRESS, false);
            curl_setOpt($ch, CURLOPT_PROGRESSFUNCTION, $returnFunction);
        }

        $resource = curl_exec($ch);

        if (strlen($resource) < 10)
            $resource = file_get_contents($sourceUrl);

        file_put_contents($targetUrl, $resource);

        if (curl_error($ch)) {
            curl_close($ch);
            return false;
        }

        curl_close($ch);

        return true;
    }

    protected function clearFileCache() : void
    {
        $cacheDirs = [$this->thumbnailTempDir, $this->previewTempDir];
        $currentTimestamp = (new \DateTime())->getTimestamp();

        foreach ($cacheDirs as $cacheDir) {
            if ($cacheDir && file_exists($cacheDir)) {
                foreach (scandir($cacheDir) as $file) {
                    if (!in_array($file, ['.', '..', 'preview'])) {
                        $filePath = preg_replace('/\?v=[\d]+$/', '', $cacheDir.$file);
                        $fileTime = filemtime($filePath);

                        if ($fileTime + $this->localThumbnailsLifetime <= $currentTimestamp) {
                            unlink($filePath);
                        }
                    }
                }
            }
        }
    }

    /**
     * Gets file header response data;
     *
     * @param string $fileUrl
     * @return array
     */
    protected function getFileHeadersData(string $fileUrl) : array
    {
        $ch = curl_init($fileUrl);
        $headersData = [];

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);

        curl_exec($ch);

        $headersData['size'] = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        $headersData['mimeType'] = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        curl_close($ch);

        return $headersData;
    }

    /**
     * Read cache data based on request;
     *
     * @param ParserRequest $parserRequest
     * @return ParserRequest
     * @throws \Exception
     */
    protected function getParserCache(ParserRequest $parserRequest) : ?ParserRequest
    {
        if (!$parserRequest->isIgnoreCache()) {
            $cacheKey = $this->determineCacheKey($parserRequest);

            if ($this->cache->has($cacheKey)) {
                $cacheData = $this->cache->get($cacheKey);

                if (!empty($cacheData)) {
                    $cachedParserRequest = new ParserRequest();

                    $this->modelConverter->setData($cacheData, $cachedParserRequest, true);

                    return $cachedParserRequest;
                }
            }
        }

        return null;
    }

    /**
     * Proxy for cache->set() function with additional key prefix
     *
     * @param string $key
     * @param $value
     * @param int $expirationTime (0 - unlimited)
     * @throws \ReflectionException
     */
    protected function setParserCache(ParserRequest $parserRequest, $expirationTime = 10) : void
    {
        $key = $this->determineCacheKey($parserRequest);

        $data = $this->modelConverter->convert($parserRequest);

        $this->cache->set($key, $data, $expirationTime);
    }

    /**
     * Clearing cache for parser request;
     *
     * @param ParserRequest $parserRequest
     */
    public function clearParserCache(ParserRequest $parserRequest): void
    {
        $this->cache->remove(
            $this->determineCacheKey($parserRequest)
        );
    }

    /**
     * Determines cache key based on ParserRequestModel
     *
     * @param ParserRequest $parserRequest
     * @return string - cache key
     */
    protected function determineCacheKey(ParserRequest $parserRequest) : string
    {
        $cacheKey = $this->parserName.'_'.$parserRequest->currentNode->getLevel();

        if ($parserRequest->pagination->active) {
            $pagination = $parserRequest->pagination;

            switch ($pagination->mode) {
                case PaginationMode::Letters:
                    $cacheKey .= '_'.$pagination->currentLetter;
                    break;

                case PaginationMode::Numbers:
                    $cacheKey .= '_'.$pagination->currentPage;
                    break;

                case PaginationMode::LoadMore:
                    $cacheKey .= '_load_more_mode';
                    break;
            }
        }

        if ($parserRequest->currentNode->getIdentifier()) {
            $cacheKey .= '_'.$parserRequest->currentNode->getIdentifier();
        }

        return $cacheKey;
    }

    /**
     * @return int
     */
    public function getTestOwnersLimit(): int
    {
        return $this->testOwnersLimit;
    }

    /**
     * @param int $testOwnersLimit
     * @return AbstractParser
     */
    public function setTestOwnersLimit(int $testOwnersLimit = -1): AbstractParser
    {
        $this->testOwnersLimit = $testOwnersLimit;
        return $this;
    }

    public function testOwnersLimitReached(int $check = 0): bool
    {
        return ($this->getTestOwnersLimit() > -1 && $check >= $this->getTestOwnersLimit());
    }

    /**
     * @return int
     */
    public function getTestBoardsLimit(): int
    {
        return $this->testBoardsLimit;
    }

    /**
     * @param int $testBoardsLimit
     * @return AbstractParser
     */
    public function setTestBoardsLimit(int $testBoardsLimit = -1): AbstractParser
    {
        $this->testBoardsLimit = $testBoardsLimit;
        return $this;
    }

    public function testBoardsLimitReached(int $check = 0): bool
    {
        return ($this->getTestBoardsLimit() > -1 && $check >= $this->getTestBoardsLimit());
    }

    /**
     * @return int
     */
    public function getTestGalleryImagesLimit(): int
    {
        return $this->testGalleryImagesLimit;
    }

    /**
     * @param int $testGalleryImagesLimit
     * @return AbstractParser
     */
    public function setTestGalleryImagesLimit(int $testGalleryImagesLimit = -1): AbstractParser
    {
        $this->testGalleryImagesLimit = $testGalleryImagesLimit;
        return $this;
    }

    public function testGalleryImagesLimitReached(int $check = 0): bool
    {
        return ($this->getTestGalleryImagesLimit() > -1 && $check >= $this->getTestGalleryImagesLimit());
    }

    /**
     * @return int
     */
    public function getTestGalleriesLimit(): int
    {
        return $this->testGalleriesLimit;
    }

    /**
     * @param int $testGalleriesLimit
     * @return AbstractParser
     */
    public function setTestGalleriesLimit(int $testGalleriesLimit = -1): AbstractParser
    {
        $this->testGalleriesLimit = $testGalleriesLimit;
        return $this;
    }

    public function testGalleriesLimitReached(int $check = 0): bool
    {
        return ($this->getTestGalleriesLimit() > -1 && $check >= $this->getTestGalleriesLimit());
    }
}