<?php

namespace App\Tests\Functional\Controller\Api;

use App\Converter\EntityConverter;
use App\Enum\FileStatus;
use App\Manager\Object\FileManager;
use App\Manager\Object\NodeManager;
use App\Service\ParserService;
use App\Tests\Functional\Controller\BasicControllerTestcase;
use App\Utils\AppHelper;
use App\Utils\StringHelper;
use App\Utils\TestsHelper;

class FileControllerTest extends BasicControllerTestcase
{
    protected $filesData = [];

    public function setUp()
    {
        parent::setUp();

        $this->createSampleFilesData();
    }

    private function getFileManager(): FileManager
    {
        return $this->containerInstance->get(FileManager::class);
    }

    public function testToggleFileQueue()
    {
        $this->executeAnonymousUserRequest($this->client, 'api_file_toggle_queue', "POST", true);

        $fileManager = $this->getFileManager();

        foreach ($this->filesData as $fileData) {
            $this->executeAdminUserRequest($this->client, 'api_file_toggle_queue', 'POST', false, $fileData);

            $response = $this->clientResponseAssertions($this->client);

            $this->assertTrue(StringHelper::isJson($response->getContent()));

            $json = json_decode($response->getContent());

            $this->assertEquals($fileData['identifier'], $json->identifier);
            $this->assertNotEmpty($json->status);

            $toggledFile = $fileManager->getOneBy(['identifier' => $fileData['identifier'], 'parser' => $fileData['parser']]);

            $this->assertNotEmpty($toggledFile);
            $this->assertNotNull($toggledFile->getId());

            $fileManager->remove($toggledFile);
        }
    }

    public function testToggleFilePreview()
    {
        $this->executeAnonymousUserRequest($this->client, 'api_file_toggle_preview', "POST", true);

        foreach ($this->filesData as $index => $fileData) {
            $this->executeAdminUserRequest($this->client, 'api_file_toggle_preview', 'POST', false, $fileData);

            $response = $this->clientResponseAssertions($this->client);

            $this->assertTrue(StringHelper::isJson($response->getContent()));

            $json = json_decode($response->getContent());

            $this->assertEquals($fileData['identifier'], $json->identifier);
            $this->assertNotEmpty($json->status);
            $this->assertNotEmpty($json->localUrl);

            $filePath = AppHelper::getPublicDir().DIRECTORY_SEPARATOR.$json->localUrl;

            $this->assertFileExists($filePath);

            $this->filesData[$index]['localUrl'] = $json->localUrl;
        }
    }

    public function testSavePreviewedFile()
    {
        $this->executeAnonymousUserRequest($this->client, 'api_file_download_preview', 'POST', true);

        $fileManager = $this->getFileManager();
        $parserService = $this->containerInstance->get(ParserService::class);
        $user = $this->loadUserByUsername(TestsHelper::$testAdminUser['username']);
        $parsersArray = [];

        foreach ($this->filesData as $index => $fileData) {
            if (!$fileData['localUrl']) {
                continue;
            }

            $this->executeAdminUserRequest($this->client, 'api_file_download_preview', 'POST', false, $fileData);

            $response = $this->clientResponseAssertions($this->client);

            $this->assertTrue(StringHelper::isJson($response->getContent()));

            $json = json_decode($response->getContent());

            $this->assertEquals($fileData['identifier'], $json->identifier);
            $this->assertNotEmpty($json->localUrl);
            $this->assertTrue(property_exists($json->statuses, FileStatus::Downloaded));

            $toggledFile = $fileManager->getOneBy(['identifier' => $fileData['identifier'], 'parser' => $fileData['parser']]);

            $this->assertNotEmpty($toggledFile);
            $this->assertNotNull($toggledFile->getId());

            if (!array_key_exists($toggledFile->getParser(), $parsersArray)) {
                $parsersArray[$toggledFile->getParser()] = $parserService->loadParser($toggledFile->getParser(), $user);
            }

            $parsersArray[$toggledFile->getParser()]->prepareFileTargetDirectories($toggledFile);

            $this->assertFileExists($toggledFile->getTargetFilePath());

            $fileManager->remove($toggledFile);
        }
    }

    private function createSampleFilesData()
    {
        $nodeManager = $this->containerInstance->get(NodeManager::class);
        $entityConverter = new EntityConverter();
        $entityConverter->setEntityManager($this->containerInstance->get('doctrine.orm.entity_manager'));

        foreach (TestsHelper::$sampleParserFiles as $sampleParserFile) {
            $sampleFileData = array_merge(TestsHelper::generateFileArray(), $sampleParserFile);

            $parentNode = $nodeManager->get($sampleFileData['parentNode']['id']);
            $sampleFileData['parentNode'] = $entityConverter->convert($parentNode);

            $this->filesData[] = $sampleFileData;
        }
    }
}