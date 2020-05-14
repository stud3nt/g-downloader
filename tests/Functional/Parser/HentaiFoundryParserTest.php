<?php

namespace App\Tests\Functional\Parser;

use App\Enum\ParserType;
use App\Tests\Functional\Parser\Base\BasicParserTestcase;
use App\Tests\Functional\Parser\Base\ParserTestInterface;
use Doctrine\Common\Util\Debug;

class HentaiFoundryParserTest extends BasicParserTestcase implements ParserTestInterface
{
    protected $parserName = ParserType::HentaiFoundry;

    protected $galleryUrl = 'pictures/user/a-rimbaud';

    public function testGetOwnerList()
    {
        // // NOTHING TO DO HERE - HF HAVEN'T 'OWNERS', USERS ARE STORED IN BOARDS;
        $this->assertTrue(true);
    }

    public function testGetBoardsListData()
    {
        // NOTHING TO DO IN THIS PARSER - HF HAVEN'T BOARDS LIST;
        $this->assertTrue(true);
    }

    public function testGetBoardData()
    {
        $this->loadParser($this->parserName);
        $this->prepareRequestModel();

        $this->getBoardData();

        $this->assertTrue((count($this->parserRequest->getParsedNodes()) > 0 || count($this->parserRequest->getFiles()) > 0));
    }

    public function testGetGalleryData()
    {
        $this->loadParser($this->parserName);
        $this->prepareRequestModel();

        $this->getBoardData();

        $url = $this->parserRequest->getParsedNodes()[0]->getUrl();

        $this->parserRequest->getCurrentNode()->setUrl($url);
        $this->parserRequest->setIgnoreCache(true);

        $this->parserObject->setTestGalleryImagesLimit(10);
        $this->parserObject->getGalleryData($this->parserRequest);

        $this->assertGreaterThan(0, count($this->parserRequest->getFiles()));
    }

    public function testGetFileData()
    {
        // nothing to do here;
        $this->assertTrue(true);
    }

    private function getBoardData(): void
    {
        $this->parserRequest->setParsedNodes([]);
        $this->parserRequest->getPagination()->setPagesPackageSize(2); // parsing 2 pages - full parsing is very, very time wasting;
        $this->parserRequest->getPagination()->setLetterPagination('B');

        $this->parserObject->getBoardData($this->parserRequest);
    }
}