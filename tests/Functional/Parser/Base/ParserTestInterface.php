<?php

namespace App\Tests\Functional\Parser\Base;

interface ParserTestInterface
{
    public function testLoadParser();

    public function testGetOwnerList();

    public function testGetBoardsListData();

    public function testGetBoardData();

    public function testGetGalleryData();

    public function testGetFileData();

    public function testGetFilePreview();

    public function testGenerateFileCurlRequest();

    public function testDetermineFileSubfolder();

    public function testClearParserCache();

    public function testPrepareFileTargetDirectories();
}