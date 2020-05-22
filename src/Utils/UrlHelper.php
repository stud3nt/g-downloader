<?php

namespace App\Utils;

class UrlHelper
{
    public static function prepareLocalUrl(string $filePath) : string
    {
        $url = str_replace(AppHelper::getPublicDir(), '', $filePath);
        $url = str_replace('\\', '/', $url);

        return $url;
    }

    public static function check403(string $url) : bool
    {
        $curl = curl_init($url);

        curl_setopt_array($curl, [
            CURLOPT_NOBODY         => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return ($code === 403);
    }

    public static function fixUrl(string $url, bool $cutOffQuery = true) : string
    {
        if ($cutOffQuery) {
            $parseUrl = parse_url($url);

            if (array_key_exists('query', $parseUrl))
                $url = str_replace('?'.$parseUrl['query'], '', $url);
        }

        return str_replace([' '], ['%20'], $url);
    }
}