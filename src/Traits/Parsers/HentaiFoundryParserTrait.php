<?php

namespace App\Traits\Parsers;

use App\Parser\HentaiFoundryParser;

trait HentaiFoundryParserTrait
{
    /** @var HentaiFoundryParser */
    protected $hentaiFoundryParser;

    /** @required */
    public function setHentaiFoundryParser(HentaiFoundryParser $hentaiFoundryParser)
    {
        $this->hentaiFoundryParser = $hentaiFoundryParser;
    }
}