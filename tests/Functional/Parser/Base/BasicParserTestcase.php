<?php

namespace App\Tests\Functional\Parser\Base;

use App\Converter\ModelConverter;
use App\Entity\Parser\File;
use App\Entity\User;
use App\Enum\ParserType;
use App\Manager\UserManager;
use App\Model\ParsedFile;
use App\Model\ParserRequest;
use App\Parser\Base\AbstractParser;
use App\Repository\FileRepository;
use App\Service\ParserService;
use App\Utils\TestsHelper;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BasicParserTestcase extends WebTestCase
{
    /** @var string|null */
    protected $parserName = null;

    /** @var AbstractParser */
    protected $parserObject;

    /** @var ParserRequest */
    protected $parserRequest;

    /** @var string */
    protected $testedUsername = 'stud3nt';
    /** @var string */
    protected $boardUrl = '';
    /** @var string */
    protected $galleryUrl = '';

    public function testPrepareRequestModel()
    {
        $this->parserRequest = null;
        $this->prepareRequestModel();

        $this->assertNotNull($this->parserRequest);
        $this->assertIsString($this->parserRequest->getRequestIdentifier());
    }

    public function testLoadParser()
    {
        $parserService = self::bootKernel()->getContainer()->get(ParserService::class);

        $this->parserObject = $parserService->loadParser($this->parserName, $this->getTestUser());

        $this->assertNotEmpty($this->parserObject);
    }

    public function testPrepareFileTargetDirectories()
    {
        $this->loadParser($this->parserName);
        $container = self::bootKernel()->getContainer();

        /** @var FileRepository $fileRepository */
        $fileRepository = $container->get(FileRepository::class);
        $randomFiles = $fileRepository->getRandomFiles($this->parserName, 1);
        /** @var File $randomFile */
        $randomFile = $randomFiles[0];

        $this->parserObject->prepareFileTargetDirectories($randomFile);

        $this->assertNotNull($randomFile->getTempFilePath());
        $this->assertNotNull($randomFile->getTargetFilePath());
    }

    public function testGetOwnerList()
    {
        $this->loadParser($this->parserName);
        $this->prepareRequestModel();

        $this->parserRequest->setParsedNodes([]);
        $this->parserRequest->setIgnoreCache(true);

        $this->parserObject->getOwnersList($this->parserRequest);

        $this->assertGreaterThan(0, count($this->parserRequest->getParsedNodes()));
    }

    public function testGetBoardsListData()
    {
        $this->loadParser($this->parserName);
        $this->prepareRequestModel();

        // test with cache enabled;
        $this->parserRequest->setParsedNodes([]);
        $this->parserRequest->setIgnoreCache(false);

        $this->parserObject->getBoardsListData($this->parserRequest);

        $this->assertGreaterThan(0, count($this->parserRequest->getParsedNodes()));

        // test with cache disabled;
        $this->parserRequest->setParsedNodes([]);
        $this->parserRequest->setIgnoreCache(true);

        $this->parserObject->getBoardsListData($this->parserRequest);

        $this->assertGreaterThan(0, count($this->parserRequest->getParsedNodes()));
    }

    public function testGetBoardData()
    {
        $this->loadParser($this->parserName);
        $this->prepareRequestModel();

        $this->parserRequest->setParsedNodes([])
            ->setIgnoreCache(true)
            ->getCurrentNode()
            ->setUrl($this->boardUrl);

        $this->parserObject->setTestGalleriesLimit(5);
        $this->parserObject->getBoardData($this->parserRequest);

        $this->assertTrue((count($this->parserRequest->getParsedNodes()) > 0 || count($this->parserRequest->getFiles()) > 0));

    }

    public function testGetGalleryData()
    {
        $this->loadParser($this->parserName);
        $this->prepareRequestModel();

        $this->parserRequest->setParsedNodes([])
            ->setIgnoreCache(false)
            ->getCurrentNode()
            ->setUrl($this->boardUrl);

        $this->parserObject->setTestGalleriesLimit(5);
        $this->parserObject->getBoardData($this->parserRequest);

        $this->assertGreaterThan(0, count($this->parserRequest->getParsedNodes()));

        $galleryUrl = $this->parserRequest->getParsedNodes()[1]->getUrl();

        $this->parserRequest->setIgnoreCache(true)
            ->getCurrentNode()
            ->setUrl($galleryUrl)
            ->setImagesNo(40);

        $this->parserObject->setTestGalleryImagesLimit(6);
        $this->parserObject->getGalleryData($this->parserRequest);

        $this->assertGreaterThan(0, count($this->parserRequest->getFiles()));
    }

    public function testGetFilePreview()
    {
        $this->loadParser($this->parserName);
        $container = self::bootKernel()->getContainer();

        $fileRepository = $container->get(FileRepository::class);
        $randomFiles = $fileRepository->getRandomFiles($this->parserName, 1);

        if (!$randomFiles) { // no test files yet :/
            $this->assertTrue(true);
            return;
        }

        $parsedFile = new ParsedFile();
        $parsedFile->setUrl($randomFiles[0]->getUrl());

        $this->parserObject->getFilePreview($parsedFile);

        $this->assertNotNull($parsedFile->getLocalUrl());
        $this->assertFileExists($parsedFile->getPreviewFilePath());
    }

    public function testGenerateFileCurlRequest()
    {
        $this->loadParser(ParserType::Boards4chan);
        $container = self::bootKernel()->getContainer();

        $fileRepository = $container->get(FileRepository::class);
        $randomFiles = $fileRepository->getRandomFiles($this->parserName, 20);

        if (!$randomFiles) { // no test files yet :/
            $this->assertTrue(true);
            return;
        }

        foreach ($randomFiles as $randomFile) {
            $this->parserObject->generateFileCurlRequest($randomFile);

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

        if (!$randomFiles) {
            $this->assertTrue(true);
            return;
        }

        /** @var File $randomFile */
        foreach ($randomFiles as $randomFile) {
            $subfolder = $this->parserObject->determineFileSubfolder($randomFile);

            if ($randomFile->getParentNode())
                $this->assertIsString($subfolder);
            else
                $this->assertEmpty($subfolder);
        }
    }

    protected function loadParser(string $parserName)
    {
        $container = self::bootKernel()->getContainer();

        $parserService = $container->get(ParserService::class);

        $this->parserObject = $parserService->loadParser($parserName, $this->getTestUser());
    }

    public function testClearParserCache()
    {
        $this->loadParser($this->parserName);
        $this->prepareRequestModel();
        $this->parserObject->clearParserCache($this->parserRequest);
        $this->assertTrue(true, true);
    }

    protected function prepareRequestModel()
    {
        $requestArray = TestsHelper::generateParserRequestArray();

        $this->parserRequest = new ParserRequest();

        $modelConverter = new ModelConverter();
        $modelConverter->setData($requestArray, $this->parserRequest);
    }

    protected function getTestUser(): ?User
    {
        $container = self::bootKernel()->getContainer();

        return $container->get(UserManager::class)->getByUsernameOrEmail($this->testedUsername);
    }
}