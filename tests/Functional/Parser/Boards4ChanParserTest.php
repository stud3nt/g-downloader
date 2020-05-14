<?php

namespace App\Tests\Functional\Parser;

use App\Enum\ParserType;
use App\Tests\Functional\Parser\Base\BasicParserTestcase;
use App\Tests\Functional\Parser\Base\ParserTestInterface;

class Boards4ChanParserTest extends BasicParserTestcase implements ParserTestInterface
{
    protected $parserName = ParserType::Boards4chan;

    protected $boardUrl = 'https://boards.4chan.org/hc/catalog';
    protected $galleryUrl = 'https://boards.4chan.org/hc/catalog';

    public function testGetOwnerList()
    {
        // nothing to do here;
        $this->assertEquals(true, true);
    }

    public function testGetFileData()
    {
        // nothing to do here;
        $this->assertEquals(true, true);
    }
}