<?php

namespace App\Parser\FileService;

use App\Model\ParsedFile;
use App\Service\CurlRequest;
use PHPHtmlParser\Dom;
use PHPHtmlParser\Dom\HtmlNode;

class GfycatParser
{
    public static function isGfycat(ParsedFile $parsedFile): bool
    {
        $url = parse_url($parsedFile->getUrl())['host'];

        return (in_array($url, ['gfycat.com', 'redgifs.com']));
    }

    /**
     * @param ParsedFile $parsedFile
     * @return ParsedFile
     * @throws \PHPHtmlParser\Exceptions\ChildNotFoundException
     * @throws \PHPHtmlParser\Exceptions\CircularException
     * @throws \PHPHtmlParser\Exceptions\CurlException
     * @throws \PHPHtmlParser\Exceptions\NotLoadedException
     * @throws \PHPHtmlParser\Exceptions\StrictException
     */
    public static function completeFileData(ParsedFile &$parsedFile): ParsedFile
    {
        $domLibrary = new Dom();
        $curl = new CurlRequest();

        $dom = $domLibrary->load(
            $curl->executeSingleRequest(
                $parsedFile->getUrl()
            )
        );
        $videos = $dom->getElementsByTag('video');

        /** @var HtmlNode $video */
        /** @var HtmlNode $source */
        foreach ($videos as $video) {
            $videoClass = $video->getAttribute('class');

            if (in_array($videoClass, ['video media', 'video'])) {
                $sources = $video->find('source');

                foreach ($sources as $source) {
                    $src = $source->getAttribute('src');

                    if ((strpos($src, 'thumbs.gfycat.com') || strpos($src, 'redgifs.com')) && strpos($src, '.webm')) {
                        $parsedFile->setFileUrl($src);
                        $parsedFile->setExtension('webm');
                        break;
                    }
                    
                    if (strpos($src, '.webm')) {
                        $parsedFile->setFileUrl($src);
                        $parsedFile->setExtension('webm');
                    }
                }
            }
        }

        return $parsedFile;
    }
}