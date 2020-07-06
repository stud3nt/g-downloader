<?php

namespace App\Tests\Functional\Controller\Api;

use App\Entity\Parser\File;
use App\Manager\Object\FileManager;
use App\Tests\Functional\Controller\BasicControllerTestcase;

class DownloaderControllerTest extends BasicControllerTestcase
{
    public function testDownloadProcess()
    {
        $this->executeAnonymousUserRequest($this->client, 'api_start_downloader_process');

        // execute request as logged user;
        $this->loginUserIntoClient('stud3nt', $this->client);
        $this->client->request('GET', $this->router->generate('api_start_downloader_process'));

        $response = $this->client->getResponse();

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsString($response->getContent());

        $jsonResponse = json_decode($response->getContent());

        $fileManager = $this->containerInstance->get(FileManager::class);
        $filesForDownload = $fileManager->getQueuedFiles(6);

        // reverting file downloading;
        if ($filesForDownload) {
            /** @var File $file */
            foreach ($filesForDownload as $file) {
                $file->setDownloadedAt(null)->setDuplicateOf(null);
                $fileManager->save($file);
            }
        }

        $this->assertEquals(1, $jsonResponse->status);
        $this->assertEquals(count($filesForDownload), $jsonResponse->data->filesCount);
    }

    /**
     * Favicon load test;
     */
    public function testStopDownload()
    {
        $this->executeAnonymousUserRequest($this->client, 'api_stop_downloader_process');

        // execute request as logged user;
        $this->loginUserIntoClient('stud3nt', $this->client);
        $this->client->request('GET', $this->router->generate('api_start_downloader_process'));

        $response = $this->client->getResponse();

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsString($response->getContent());

        $jsonResponse = json_decode($response->getContent());

        $this->assertEquals(1, $jsonResponse->status);
    }
}