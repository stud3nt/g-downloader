<?php

namespace App\Tests\Functional\Parser;

use App\Enum\ParserType;
use App\Tests\Functional\Parser\Base\BasicParserTestcase;
use App\Tests\Functional\Parser\Base\ParserTestInterface;

class ImageFapParserTest extends BasicParserTestcase implements ParserTestInterface
{
    protected $parserName = ParserType::Imagefap;

    protected $boardUrl = 'https://www.imagefap.com/showfavorites.php?userid=1613432&folderid=3023725';
    protected $galleryUrl = 'https://www.imagefap.com/pictures/4863376/Black-Babe---White-Cock-002';

    public function testGetOwnerList()
    {
        $this->loadParser($this->parserName);
        $this->prepareRequestModel();

        $this->parserRequest->setParsedNodes([])
            ->setIgnoreCache(true)
            ->setSorting(['page' => 0]);

        $this->parserObject->getOwnersList($this->parserRequest);

        $this->assertGreaterThan(0, count($this->parserRequest->getParsedNodes()));
    }

    public function testGetBoardsListData()
    {
        $this->loadParser($this->parserName);
        $this->prepareRequestModel();

        // test with cache disabled;
        $this->parserRequest->setParsedNodes([])
            ->setIgnoreCache(true)
            ->getCurrentNode()
            ->setName('Jasondayfap83')
            ->setIdentifier('1481674');

        $this->parserObject->getBoardsListData($this->parserRequest);

        $this->assertGreaterThan(0, count($this->parserRequest->getParsedNodes()));

    }

    public function testGetFileData()
    {
        // nothing to do here;
        $this->assertTrue(true);
    }
}