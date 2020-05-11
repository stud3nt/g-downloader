<?php

namespace App\Tests\Functional\Parser;

use App\Entity\Parser\File;
use App\Enum\FolderType;
use App\Enum\ParserType;
use App\Manager\UserManager;
use App\Model\ParsedFile;
use App\Repository\FileRepository;
use App\Service\ParserService;
use App\Tests\Functional\Parser\Base\BasicParserTest;
use App\Tests\Functional\Parser\Base\ParserTestInterface;

class Boards4ChanParserTest extends BasicParserTest implements ParserTestInterface
{
    private $parserName = ParserType::Boards4chan;

    public function testLoadParser()
    {
        $container = self::bootKernel()->getContainer();

        $parserService = $container->get(ParserService::class);
        $userManager = $container->get(UserManager::class);

        $this->parser = $parserService->loadParser($this->parserName, $userManager->getByUsernameOrEmail('stud3nt'));

        $this->assertNotEmpty($this->parser);
    }

    public function testPrepareFileTargetDirectories()
    {
        $this->loadParser(ParserType::Boards4chan);
        $container = self::bootKernel()->getContainer();

        $fileRepository = $container->get(FileRepository::class);
        $randomFiles = $fileRepository->getRandomFiles($this->parserName, 1);
        /** @var File $randomFile */
        $randomFile = $randomFiles[0];

        $this->parser->prepareFileTargetDirectories($randomFile);

        $this->assertNotNull($randomFile->getTempFilePath());
        $this->assertNotNull($randomFile->getTargetFilePath());
    }

    public function testGetOwnerList()
    {
        // nothing to do here;
        $this->assertEquals(true, true);
    }

    public function testGetBoardsListData()
    {
        $this->loadParser(ParserType::Boards4chan);
        $this->prepareRequestModel();

        // test with cache enabled;
        $this->parserRequest->setParsedNodes([]);
        $this->parserRequest->setIgnoreCache(false);
        $this->parser->getBoardsListData($this->parserRequest);

        $this->assertGreaterThan(0, count($this->parserRequest->getParsedNodes()));

        // test with cache disabled;
        $this->parserRequest->setParsedNodes([]);
        $this->parserRequest->setIgnoreCache(true);
        $this->parser->getBoardsListData($this->parserRequest);

        $this->assertGreaterThan(0, count($this->parserRequest->getParsedNodes()));
    }

    public function testGetBoardData()
    {
        $this->loadParser(ParserType::Boards4chan);
        $this->prepareRequestModel();

        $this->parserRequest->setParsedNodes([]);
        $this->parserRequest->getCurrentNode()->setUrl('https://boards.4chan.org/hc/catalog');
        $this->parser->getBoardData($this->parserRequest);

        $this->assertGreaterThan(0, count($this->parserRequest->getParsedNodes()));
    }

    public function testGetGalleryData()
    {
        $this->loadParser(ParserType::Boards4chan);
        $this->prepareRequestModel();

        $this->parserRequest->setParsedNodes([]);
        $this->parserRequest->getCurrentNode()->setUrl('https://boards.4chan.org/hc/catalog');
        $this->parser->getBoardData($this->parserRequest);

        $this->assertGreaterThan(0, count($this->parserRequest->getParsedNodes()));

        $url = $this->parserRequest->getParsedNodes()[1]->getUrl();

        $this->parserRequest->getCurrentNode()->setUrl($url);
        $this->parser->getGalleryData($this->parserRequest);
    }

    public function testGetFileData()
    {
        // nothing to do here;
        $this->assertEquals(true, true);
    }

    public function testGetFilePreview()
    {
        $this->loadParser(ParserType::Boards4chan);
        $container = self::bootKernel()->getContainer();

        $fileRepository = $container->get(FileRepository::class);
        $randomFiles = $fileRepository->getRandomFiles($this->parserName, 1);

        $parsedFile = new ParsedFile();
        $parsedFile->setUrl($randomFiles[0]->getUrl());

        $this->parser->getFilePreview($parsedFile);

        $this->assertNotNull($parsedFile->getLocalUrl());
        $this->assertFileExists($parsedFile->getPreviewFilePath());
    }

    public function testGenerateFileCurlRequest()
    {
        $this->loadParser(ParserType::Boards4chan);
        $container = self::bootKernel()->getContainer();

        $fileRepository = $container->get(FileRepository::class);
        $randomFiles = $fileRepository->getRandomFiles($this->parserName, 20);

        foreach ($randomFiles as $randomFile) {
            $this->parser->generateFileCurlRequest($randomFile);

            $curlRequest = $randomFile->getCurlRequest();

            $this->assertNotEmpty($curlRequest);
            $this->assertIsResource($curlRequest);
        }
    }

    public function testDetermineFileSubfolder()
    {
        $this->loadParser(ParserType::Boards4chan);
        $container = self::bootKernel()->getContainer();

        $fileRepository = $container->get(FileRepository::class);
        $randomFiles = $fileRepository->getRandomFiles($this->parserName, 5);

        /** @var File $randomFile */
        foreach ($randomFiles as $randomFile) {
            $fileSettings = $randomFile->getFinalNodeSettings();
            $subfolder = $this->parser->determineFileSubfolder($randomFile);

            if ((
                $fileSettings
                &&
                ($fileSettings->getFolderType() !== FolderType::CustomText
                ||
                $fileSettings->getFolderType() === FolderType::CustomText && !empty($fileSettings->getFolderType()))
            ) || $randomFile->getParentNode()) {
                $this->assertNotEmpty($subfolder);
            } else {
                $this->assertEmpty($subfolder);
            }
        }
    }

    public function testClearParserCache()
    {
        $this->loadParser(ParserType::Boards4chan);
        $this->prepareRequestModel();
        $this->parser->clearParserCache($this->parserRequest);
        $this->assertTrue(true, true);
    }
}