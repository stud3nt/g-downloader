<?php

namespace App\Traits\Parsers;

use App\Parser\Boards4chanParser;

trait Boards4chanParserTrait
{
    /** @var Boards4chanParser $boards4chanParser */
    protected $boards4chanParser;

    /** @required */
    public function setBoards4chanParser(Boards4chanParser $boards4chanParser)
    {
        $this->boards4canParser = $boards4chanParser;
    }
}