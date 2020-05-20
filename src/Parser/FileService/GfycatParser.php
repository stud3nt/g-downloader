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
        return (parse_url($parsedFile->getUrl())['host'] === 'gfycat.com');
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

                    if (strpos($src, 'thumbs.gfycat.com') && strpos($src, '.mp4')) {
                        $parsedFile->setFileUrl($src);
                        $parsedFile->setExtension('mp4');
                        break;
                    }
                }
            }
        }

        return $parsedFile;
    }
}