<?php

namespace App\Service\Reddit;

use App\Model\ParserRequest;
use App\Model\SettingsModel;
use GuzzleHttp\Client;
use Symfony\Component\Cache\CacheItem;

class RedditApi
{
    /** @var RedditOauth */
    private $oauth;

    /** @var Client */
    private $http;

    protected $afterToken;
    protected $beforeToken;

    /**
     * @param SettingsModel $redditSettings
     * @return $this
     * @throws \Exception
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function init(SettingsModel $settings)
    {
        $this->http = new Client();
        $this->oauth = (new RedditOauth())->init($settings);

        return $this;
    }

    /**
     * @param $after - 'after' string
     * @throws \Psr\Cache\InvalidArgumentException
     * @return \stdClass
     */
    public function getSubredditsList(string $after = null)
    {
        $params = array(
            'after' => $after,
            'before' => null,
            'count' => 100,
            //'limit' => 100,
            'show' => 'all'
        );

        return $this->apiCall("/subreddits/mine/subscriber", 'GET', $params);
    }

    /**
     * @param ParserRequest $parserRequest
     * @param string|null $after
     * @return bool|mixed|string
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getSubreddit(ParserRequest $parserRequest)
    {
        $callOptions = array(
            'after' => $parserRequest->tokens->after,
            'before' => null,
            'count' => 100,
            //'limit' => 100,
            'preview' => true,
            'thumbnail' => true,
            'show' => 'all',
            'rtj' => 'only',
            'redditWebClient' => 'web2x',
            'app' => 'web2x-client-production',
            'allow_over18' => 1,
            'include' => 'identity',
            'sort' => 'hot',
            'geo_filter' => 'PL',
            'layout' => 'card',
            'thumbnail_height' => 200,
            'thumbnail_width' => 200
        );

        return $this->apiCall('/'.$parserRequest->currentNode->url."/hot", "GET", $callOptions);
    }

    /**
     * @return bool|mixed|string
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getPrefs()
    {
        return $this->apiCall('/api/v1/me/prefs', 'GET');
    }

    /**
     * @param $path
     * @param string $method
     * @param null $params
     * @param bool $json
     * @return bool|mixed|string
     * @throws \Exception
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function apiCall($path, $method = 'POST', $params = null)
    {
        //Prepare request URL
        $url = 'https://oauth.reddit.com'.$path.'?raw_json=1';

        //Obtain access token for authentication
        $this->oauth->refreshAccessToken();

        //Prepare cURL options
        $options[CURLOPT_RETURNTRANSFER] = true;
        $options[CURLOPT_CONNECTTIMEOUT] = 10;
        $options[CURLOPT_TIMEOUT] = 30;
        $options[CURLOPT_USERAGENT] = $this->oauth->getUserAgent();
        $options[CURLOPT_CUSTOMREQUEST] = $method;
        $options[CURLOPT_HTTPHEADER][] = $this->oauth->getHttpHeader();

        $params['raw_json'] = 1;

        if (isset($params)) {
            if ($method == 'GET') {
                $url .= '&' . http_build_query($params);
            } else {
                $options[CURLOPT_POSTFIELDS] = $params;
            }
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $rawResponse = curl_exec($ch);
        curl_close($ch);

        $processedResponse = json_decode($rawResponse);

        return $processedResponse;
    }

}