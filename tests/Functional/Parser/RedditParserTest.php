<?php

namespace App\Tests\Functional\Parser;

use App\Entity\Parser\File;
use App\Enum\FolderType;
use App\Enum\ParserType;
use App\Model\ParsedFile;
use App\Repository\FileRepository;
use App\Tests\Functional\Parser\Base\BasicParserTestcase;
use App\Tests\Functional\Parser\Base\ParserTestInterface;

class RedditParserTest extends BasicParserTestcase implements ParserTestInterface
{
    protected $parserName = ParserType::Reddit;

    protected $boardUrl = '/r/Boobies';
    protected $galleryUrl = '/r/Boobies';

    public function testGetOwnerList()
    {
        // nothing to do here - reddit's user are not parsed yet;
        $this->assertTrue(true);
    }

    public function testGetGalleryData()
    {
        // nothing to do here => galleries are downloaded in getBoardData() function;
        $this->assertTrue(true);
    }

    public function testGetFileData()
    {
        // nothing to do here;
        $this->assertTrue(true);
    }
}