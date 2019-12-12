<?php

namespace App\Parser\Base;

use App\Entity\Parser\Board;
use App\Entity\Parser\File;
use App\Entity\Parser\Gallery;
use App\Enum\FileType;
use App\Manager\SettingsManager;
use App\Utils\AppHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use PHPHtmlParser\Dom;
use Symfony\Component\Filesystem\Filesystem;

abstract class BasicParser
{
    // parser name
    protected $parserName;

    // main page url
    protected $mainUrl;

    // main media url (optional);
    protected $mediaMainUrl = null;

    /** @var \DOMDocument */
    protected $dom;

    /** @var Client */
    protected $httpClient;

    /** @var Dom */
    protected $domLibrary;

    /** @var CookieJar */
    protected $cookieJar;

    // cookie directory location
    protected $cookieDir;

    // does site use cookie?
    protected $useCookies = false;

    protected $thumbnailTemp;

    protected $thumbnailFolder;

    /** @var SettingsManager $settingsManager */
    protected $settingsManager;

    /** @var SettingsManager $settingsManager */
    protected $filesManager;

    public function __construct()
    {
        $this->httpClient = new Client();
        $this->cookieJar = new CookieJar();
        $this->domLibrary = new Dom();

        $ds = DIRECTORY_SEPARATOR;

        $this->thumbnailFolder = 'temp'.$ds.'parsers'.$ds.$this->parserName.$ds;
        $this->thumbnailTemp = AppHelper::getPublicDir().$this->thumbnailFolder;
    }

    /** @required */
    public function getManagers(SettingsManager $settingsManager)
    {
        $this->settingsManager = $settingsManager;
    }

    /**
     * Download file into specific destination folder;
     *
     * @param string $fileUrl
     * @param string $destinationFile
     * @return bool
     */
    public function downloadFile(string $fileUrl, string $destinationFile) : bool
    {
        $file = fopen($destinationFile, 'w+');

        try {
            $response = $this->httpClient->get($fileUrl, ['sink' => $file]);

            if ($response->getStatusCode() == 200) {
                return true;
            }
        } catch (ClientException $ce) {
            fclose($file);
            unlink($destinationFile);
        }

        return false;
    }

    protected function resetDom() : void
    {
        $this->dom = null;
    }

    /**
     * Loads DOM structure from URL;
     *
     * @param string $url
     * @return void
     */
    public function loadDomFromUrl(string $url) : void
    {
        $this->dom = $this->domLibrary->loadFromUrl($url);
    }

    /**
     * Preparing file preview;
     *
     * @param array $file
     * @return array
     */
    public function preparePreview(array &$file)
    {
        if ($file['sourceUrl'] && $file['type'] === FileType::Image) {
            $pathinfo = pathinfo($file['sourceUrl']);
            $extension = $pathinfo['extension'];
            $filename = 'preview.'.$extension;
            $destinationPath = $this->thumbnailTemp.$filename;

            if ($this->downloadFile($file['sourceUrl'], $destinationPath)) {
                $file['previewUrl'] = '/'.$this->thumbnailFolder.$filename.'?v='.time();
            }
        }

        return $file;
    }

    /**
     * @param int $hours
     * @throws \Exception
     */
    public function deleteOldThumbnails(int $hours = 48) : void
    {
        $fs = new Filesystem();
        $compareDateObj = (new \DateTime())->modify('-'.$hours.' hours');

        foreach (scandir($this->thumbnailTemp) as $fileName) {
            if (!in_array($fileName, ['.', '..'])) {
                $filePath = $this.$this->thumbnailTemp.$fileName;
                $fileDate = filemtime($filePath);
                $fileDateObj = new \DateTime($fileDate);

                if ($fileDateObj < $compareDateObj) {
                    $fs->remove($filePath);
                }
            }
        }
    }

    public function determineFileFormat(string $webSourceUrl)
    {
        $pathinfo = pathinfo($webSourceUrl);

        return strtoupper($pathinfo['extension']);
    }

    public function determineFileType(string $webSourceUlr)
    {
        $format = $this->determineFileFormat($webSourceUlr);

        switch ($format) {
            case 'WEBM':
            case 'MP4':
            case 'MKV':
            case 'WMV':
            case 'FLASH':
                return FileType::Video;
                break;

            default:
                return FileType::Image;
        }
    }
}