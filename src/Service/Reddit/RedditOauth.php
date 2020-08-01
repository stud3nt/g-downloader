<?php

namespace App\Service\Reddit;

use App\Enum\ParserType;
use App\Model\SettingsModel;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\CacheItem;

class RedditOauth
{
    const CACHE_NAME = 'auth_token.reddits';

    private $expiration;
    private $scope;
    private $password;
    private $appId;
    private $appSecret;

    public $accessToken;
    public $tokenType;
    public $userAgent;
    public $endpoint;
    public $username;

    public $token;

    /** @var FilesystemAdapter */
    private $cache;

    /**
     * @param SettingsModel $settings
     * @return $this
     * @throws \Exception
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function init(SettingsModel $settings)
    {
        $this->username = $settings->getParserSetting(ParserType::Reddit, 'username');
        $this->password = $settings->getParserSetting(ParserType::Reddit, 'password');
        $this->appId = $settings->getParserSetting(ParserType::Reddit, 'appId');
        $this->appSecret = $settings->getParserSetting(ParserType::Reddit, 'appSecret');
        $this->userAgent = $settings->getParserSetting(ParserType::Reddit, 'userAgent');
        $this->endpoint = $settings->getParserSetting(ParserType::Reddit, 'endpoint');
        $this->expiration = (new \DateTime())->modify('-1 second');

        $this->cache = new FilesystemAdapter();

        $this->refreshAccessToken();

        return $this;
    }

    /**
     * Loads access token from cache - or gets him from API;
     *
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Exception
     */
    public function refreshAccessToken() : void
    {
        $this->requestAccessToken();
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function getUserAgent()
    {
        return $this->userAgent;
    }

    public function getHttpHeader()
    {
        return "Authorization: ".$this->tokenType." ".$this->accessToken;
    }

    /**
     * @return bool
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function loadStoredAccessToken() : bool
    {
        /** @var CacheItem $tokenCache */
        $tokenCache = $this->cache->getItem(self::CACHE_NAME);

        if ($tokenData = $tokenCache->get()) {
            $token = json_decode($tokenData);

            $this->accessToken = $token->accessToken;
            $this->tokenType = $token->tokenType;
            $this->expiration = $token->expiration;
            $this->scope = $token->scope;

            return true;
        }

        return false;
    }

    /**
     * @return bool
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Exception
     */
    private function requestAccessToken() : bool
    {
        $url ='https://ssl.reddit.com/api/v1/access_token';
        $clientId = $this->appId;
        $clientSecret = $this->appSecret;

        // post variables
        $fields = array (
            'grant_type' => 'client_credentials',
            'username' => $this->username,
            'password' => $this->password
        );

        $userAgent = 'sometext:appnamehere v0.1 by usernamehere';

        // prepare data for post
        $field_string = http_build_query($fields);

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret) ));
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl,CURLOPT_USERAGENT, $userAgent);
        curl_setopt($curl,CURLOPT_POST, 1);
        curl_setopt($curl,CURLOPT_POSTFIELDS, $field_string);

        $response = curl_exec($curl);
        curl_close($curl);


        $response = json_decode($response);

        if (!isset($response->access_token)) {
            if (isset($response->error)) {
                if ($response->error === "invalid_grant") {
                    throw new \Exception("Supplied reddit username/password are invalid or the threshold for invalid logins has been exceeded.");
                } elseif ($response->error === 401) {
                    throw new \Exception("Supplied reddit app ID/secret are invalid.");
                }
            }
        }

        $token = new \stdClass();
        $token->accessToken = $response->access_token;
        $token->tokenType = $response->token_type;
        $token->expiration = (new \DateTime())->modify('+'.($response->expires_in - 10).' seconds');
        $token->scope = $response->scope;

        /** @var CacheItem $tokenCache */
        $tokenCache = $this->cache->getItem(self::CACHE_NAME);
        $tokenCache->set(json_encode($token));
        $tokenCache->expiresAt($token->expiration);

        $this->cache->save($tokenCache);

        $this->loadStoredAccessToken();

        return true;
    }
}