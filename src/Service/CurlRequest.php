<?php

namespace App\Service;

use App\Utils\AppHelper;

class CurlRequest
{
    // curl handle
    protected $multiCurlHandle = null;

    protected $curlRequests = [];

    protected $cookieFile;

    public function init(string $pageName) : self
    {
        $this->cookieFile = AppHelper::getDataDir().'cookie-temp-'.$pageName.'.txt';

        return $this;
    }

    public function startMultiCurl() : self
    {
        if (!$this->multiCurlHandle) {
            $this->multiCurlHandle = curl_multi_init();
        }

        return $this;
    }

    public function addRequest(string $url, array $options = []) : self
    {
        $this->startMultiCurl();

        $request = $this->prepareCurlRequest($url, $options);

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
    public function executeSingleRequest(string $url, array $options = [])
    {
        $curlRequest = $this->prepareCurlRequest($url, $options);

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

    protected function prepareCurlRequest(string $url, array $options = [])
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0)");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);

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