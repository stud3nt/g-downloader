<?php

namespace App\Parser\Base;

use App\Converter\ModelConverter;
use App\Enum\NodeLevel;
use App\Enum\PaginationMode;
use App\Manager\SettingsManager;
use App\Model\ParserRequestModel;
use App\Service\FileCache;
use App\Service\CurlRequest;
use App\Traits\PageLoaderTrait;
use App\Utils\AppHelper;
use GuzzleHttp\Client;
use PHPHtmlParser\Dom;

class AbstractParser
{
    use PageLoaderTrait;

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

    /** @var SettingsManager */
    protected $settingsManager;

    /** @var ModelConverter */
    protected $modelConverter;

    // local folders folders
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

    public function __construct(SettingsManager $settingsManager, ModelConverter $modelConverter)
    {
        $this->httpClient = new Client();
        $this->domLibrary = new Dom();
        $this->curlRequest = (new CurlRequest())->init($this->parserName);
        $this->cache = new FileCache();

        $this->settingsManager = $settingsManager;
        $this->modelConverter = $modelConverter;

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

        $this->setPageLoaderProgress(5);
    }

    public function __destruct()
    {
        $this->setPageLoaderProgress(100);
        $this->clearCache();
    }

    protected function loadDomFromUrl(string $url) : Dom
    {
        return $this->domLibrary->load(
            $this->curlRequest->executeSingleRequest($url)
        );
    }

    protected function loadDomFromHTML(string $html) : Dom
    {
        return $this->domLibrary->load($html);
    }

    protected function loadHtmlFromUrl(string $url) : string
    {
        return $this->curlRequest->executeSingleRequest($url);
    }

    protected function downloadFile(string $sourceUrl, string $targetUrl)
    {
        $ch = curl_init();

        $targetFile = fopen($targetUrl, 'w+');

        curl_setopt($ch, CURLOPT_FILE, $targetFile);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $sourceUrl);

        curl_exec($ch);
        fclose($targetFile);

        if (curl_error($ch)) {
            curl_close($ch);
            return false;
        }

        curl_close($ch);

        return true;
    }

    protected function clearCache() : void
    {
        $cacheDirs = [$this->thumbnailTempDir, $this->previewTempDir];
        $currentTimestamp = (new \DateTime())->getTimestamp();

        foreach ($cacheDirs as $cacheDir) {
            if ($cacheDir && file_exists($cacheDir)) {
                foreach (scandir($cacheDir) as $file) {
                    if (!in_array($file, ['.', '..'])) {
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
     * @param ParserRequestModel $parserRequestModel
     * @return ParserRequestModel
     * @throws \Exception
     */
    protected function getParserCache(ParserRequestModel &$parserRequestModel) : ?ParserRequestModel
    {
        if (!$parserRequestModel->ignoreCache) {
            $cacheKey = $this->determineCacheKey($parserRequestModel);

            if ($this->cache->has($cacheKey)) {
                $parserRequestModel->parsedNodes = [];
                $parserRequestModel->files = [];

                $cacheData = $this->cache->get($cacheKey);

                if (!empty($cacheData)) {
                    $this->modelConverter->setData($cacheData, $parserRequestModel, true);

                    return $parserRequestModel;
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
     */
    protected function setParserCache(ParserRequestModel &$parserRequestModel, $expirationTime = 10) : void
    {
        $key = $this->determineCacheKey($parserRequestModel);
        $data = [
            'parsedNodes' => [],
            'files' => [],
            'pagination' => [
                'mode' => null
            ]
        ];

        switch ($parserRequestModel->level) {
            case NodeLevel::Owner:
            case NodeLevel::BoardsList:
            case NodeLevel::Board:
                $data['parsedNodes'] = $parserRequestModel->parsedNodes;
                break;

            case NodeLevel::Gallery:
                $data['files'] = $parserRequestModel->files;
                break;
        }

        $data['pagination']['active'] = json_decode(json_encode($parserRequestModel->pagination->active), true);
        $data['pagination']['mode'] = json_decode(json_encode($parserRequestModel->pagination->mode), true);

        $this->cache->set($key, $data, $expirationTime);
    }

    /**
     * Determines cache key based on ParserRequestModel
     *
     * @param ParserRequestModel $parserRequestModel
     * @return string - cache key
     */
    protected function determineCacheKey(ParserRequestModel $parserRequestModel) : string
    {
        $cacheKey = $this->parserName.'_'.$parserRequestModel->level;

        if ($parserRequestModel->pagination->active) {
            $pagination = $parserRequestModel->pagination;

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

        if ($parserRequestModel->currentNode->identifier) {
            $cacheKey .= '_'.$parserRequestModel->currentNode->identifier;
        }

        return $cacheKey;
    }
}