<?php

namespace App\Service;

use App\Factory\RedisFactory;
use App\Utils\AppHelper;
use Symfony\Component\Filesystem\Filesystem;

class CurlRequest
{
    // curl handle
    protected $multiCurlHandle = null;

    protected $curlRequests = [];

    protected $cookieFile;

    public function init(string $pageName, string $userToken = null) : self
    {
        if ($userToken) {
            $ds = DIRECTORY_SEPARATOR;
            $fs = new Filesystem();
            $cookieDir = AppHelper::getDataDir().'user-cache'.$ds.$userToken;

            if (!$fs->exists($cookieDir)) {
                $fs->mkdir($cookieDir, 0777);
            }

            $this->cookieFile = $cookieDir.$ds.'cookie-temp-'.$pageName.'.txt';
        } else {
            $this->cookieFile = AppHelper::getDataDir().'cookie-temp-'.$pageName.'.txt';
        }

        return $this;
    }

    public function startMultiCurl() : self
    {
        if (!$this->multiCurlHandle) {
            $this->multiCurlHandle = curl_multi_init();
        }

        return $this;
    }

    /**
     * Adds prepared curl_request to executing array;
     *
     * @param $request
     * @return CurlRequest
     */
    public function addRequest($request, $key = null): self
    {
        $this->startMultiCurl();

        if ($key)
            $this->curlRequests[$key] = $request;
        else
            $this->curlRequests[] = $request;

        return $this;
    }

    public function addRequestFromUrl(string $url, array $options = []) : self
    {
        $this->startMultiCurl();

        $request = $this->prepareCurlRequest($url);

        if ($customKey = $options['customKey'] ?? null) {
            $this->curlRequests[$customKey] = $request;
        } else {
            $this->curlRequests[] = $request;
        }

        return $this;
    }

    /**
     * Executing single request
     *
     * @param string $url
     * @param array $options
     * @return bool|string
     */
    public function executeSingleRequest($resource, array $options = [])
    {
        if (is_string($resource))
            $curlRequest = $this->prepareCurlRequest($resource);
        else
            $curlRequest = $resource;

        $data = curl_exec($curlRequest);

        curl_close($curlRequest);

        return $data;
    }

    public function executeRequests()
    {
        if ($this->curlRequests) {
            $results = [];

            foreach ($this->curlRequests as $key => $request) { // preparing handlers;
                curl_multi_add_handle($this->multiCurlHandle, $request);
            }

            $running = null;

            do { // execute handlers
                curl_multi_exec($this->multiCurlHandle, $running);
            } while($running > 0);

            foreach($this->curlRequests as $key => $request) { // get content and remove handles
                $results[$key] = curl_multi_getcontent($request);
                curl_multi_remove_handle($this->multiCurlHandle, $request);
            }

            //$this->closeMultiCurl();
            $this->curlRequests = [];

            return $results;
        }

        return false;
    }

    public function prepareCurlRequest(string $url, string $targetUrl = null, $returnFunction = null)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0)");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 12);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 400); //timeout in seconds

        if ($returnFunction) {
            curl_setopt($ch, CURLOPT_NOPROGRESS, false);
            curl_setOpt($ch, CURLOPT_PROGRESSFUNCTION, $returnFunction);
        }

        if ($targetUrl)
            curl_setopt($ch, CURLOPT_FILE, $targetUrl);

        return $ch;
    }

    public function closeMultiCurl() : void
    {
        if ($this->multiCurlHandle) {
            curl_multi_close($this->multiCurlHandle);
        }

        $this->handle = null;
    }

    public function getCookieFile()
    {
        return $this->cookieFile;
    }

    public function __destruct()
    {
        $this->closeMultiCurl();
    }
}