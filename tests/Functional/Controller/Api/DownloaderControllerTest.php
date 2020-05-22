<?php

namespace App\Tests\Functional\Controller\Api;

use App\Manager\Object\FileManager;
use App\Tests\Functional\Controller\BasicControllerTestcase;

class DownloaderControllerTest extends BasicControllerTestcase
{
    public function testDownloadProcess()
    {
        // execute request as anonymous user;
        $this->client->request('GET', $this->router->generate('api_start_downloader_process'));

        $response = $this->client->getResponse();

        $this->assertFalse($response->isSuccessful());
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertContains('login', $response->headers->get('location'));

        // execute request as logged user;
        $this->loginUserIntoClient('stud3nt', $this->client);
        $this->client->request('GET', $this->router->generate('api_start_downloader_process'), [
            'auth' => ['stud3nt' => '1234567890']
        ]);

        $response = $this->client->getResponse();

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsString($response->getContent());

        $jsonResponse = json_decode($response->getContent());

        $fileManager = $this->containerInstance->get(FileManager::class);
        $filesForDownload = $fileManager->getQueuedFiles(6);

        $this->assertEquals(1, $jsonResponse->status);
        $this->assertEquals(count($filesForDownload), $jsonResponse->data->filesCount);
    }

    /**
     * Favicon load test;
     */
    public function testStopDownload()
    {
        $this->assertTrue(true);
    }
}