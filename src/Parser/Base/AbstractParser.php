<?php

namespace App\Parser\Base;

use App\Converter\ModelConverter;
use App\Entity\Parser\File;
use App\Entity\User;
use App\Enum\FileType;
use App\Enum\PaginationMode;
use App\Factory\RedisFactory;
use App\Model\ParserRequest;
use App\Model\SettingsModel;
use App\Service\{FileCache, CurlRequest};
use App\Utils\AppHelper;
use Doctrine\Common\Util\Debug;
use GuzzleHttp\Client;
use PHPHtmlParser\Dom;
use Symfony\Component\Filesystem\Filesystem;

class AbstractParser
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
    protected $cookieFile;

    // url's
    protected $mainBoardUrl;
    protected $mainGalleryUrl;
    protected $mainMediaUrl;

    protected $localThumbnailsLifetime = (60*60*24*7); // initial - 1 week lifetime;

    protected $sessionData = [];

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
     * Prepares file target directories and sets it to file entity;
     *
     * @param File $file
     * @return File
     */
    public function prepareFileTargetDirectories(File &$file): File
    {
        $ds = DIRECTORY_SEPARATOR;
        $fs = new Filesystem();

        $targetDirectory = $this->settings->getCommonSetting('downloadDirectory');
        $targetDirectory .= $ds.preg_replace('/[^a-zA-Z0-9\-\_]/', '_', $this->parserName);
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

        if (!$fs->exists($targetDirectory)) {
            $fs->mkdir($targetDirectory, 0777);
        }

        if ($file->getType() === FileType::Video)
            $targetFilePath = $targetDirectory.$ds.$file->getName().'.'.$file->getExtension();
        else
            $targetFilePath = $targetDirectory.$ds.$file->getName().'.jpg';

        $file->setTargetFilePath($targetFilePath);
        $file->setTempFilePath($this->previewTempDir.$file->getName().'.'.$file->getExtension());

        return $file;
    }

    /**
     * Generates CURL request for file download and defines file locations (temporary and target);
     *
     * @param File $file
     * @return File
     */
    public function generateFileCurlRequest(File &$file): File
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

    public function determineFileSubfolder(File $file): ?string
    {
        return '';
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
    protected function getParserCache(ParserRequest &$parserRequest) : ?ParserRequest
    {
        if (!$parserRequest->ignoreCache) {
            $cacheKey = $this->determineCacheKey($parserRequest);

            if ($this->cache->has($cacheKey)) {
                $cacheData = $this->cache->get($cacheKey);

                if (!empty($cacheData)) {
                    $this->modelConverter->setData($cacheData, $parserRequest, true);

                    return $parserRequest;
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
    protected function setParserCache(ParserRequest &$parserRequest, $expirationTime = 10) : void
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
}