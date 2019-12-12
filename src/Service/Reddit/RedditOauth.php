<?php

namespace App\Service\Reddit;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Debug\Debug;

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
     * @param array $redditSettings
     * @return $this
     * @throws \Exception
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function init(array $redditSettings)
    {
        $this->username = $redditSettings['reddit_username'];
        $this->password = $redditSettings['reddit_password'];
        $this->appId = $redditSettings['reddit_app_id'];
        $this->appSecret = $redditSettings['reddit_app_secret'];
        $this->userAgent = $redditSettings['reddit_user_agent'];
        $this->endpoint = $redditSettings['reddit_endpoint'];
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
        //if (!$this->loadStoredAccessToken() || $this->expiration <= (new \DateTime())) {
            $this->requestAccessToken();
        //}
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
     * @throws \Exception
     */
    private function requestAccessToken() : bool
    {
        $url = "{$this->endpoint}/api/v1/access_token";

        $params = array(
            'grant_type' => 'password',
            'username' => $this->username,
            'password' => $this->password
        );

        $options[CURLOPT_USERAGENT] = $this->userAgent;
        $options[CURLOPT_USERPWD] = $this->appId.':'.$this->appSecret;
        $options[CURLOPT_RETURNTRANSFER] = true;
        $options[CURLOPT_CONNECTTIMEOUT] = 5;
        $options[CURLOPT_TIMEOUT] = 10;
        $options[CURLOPT_CUSTOMREQUEST] = 'POST';
        $options[CURLOPT_POSTFIELDS] = $params;

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $response_raw = curl_exec($ch);
        $response = json_decode($response_raw);
        curl_close($ch);

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