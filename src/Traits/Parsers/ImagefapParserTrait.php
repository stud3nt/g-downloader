<?php

namespace App\Traits\Parsers;

use App\Parser\ImagefapParser;

trait ImagefapParserTrait
{
    /** @var ImagefapParser */
    protected $imagefapParser;

    /** @required */
    public function setImagefapParser(ImagefapParser $imagefapParser)
    {
        $this->imagefapParser = $imagefapParser;
    }
}