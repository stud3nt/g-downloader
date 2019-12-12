<?php

namespace App\Traits\Parsers;

use App\Parser\RedditParser;

trait RedditParserTrait
{
    /** @var RedditParser */
    protected $redditParser;

    /** @required */
    public function setRedditParser(RedditParser $redditParser)
    {
        $this->redditParser = $redditParser;
    }
}