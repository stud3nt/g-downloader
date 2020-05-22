<?php

namespace App\Tests\Functional\Controller\Front;

use App\Enum\FileSpecification;
use App\Tests\Functional\Controller\BasicControllerTestcase;

class IndexControllerTest extends BasicControllerTestcase
{
    protected $indexRoutes = [
        'app_index' => [],
        'app_login' => [],
        'app_logout' => [],
        'app_parser' => [
            'parserName' => null,
            'nodeLevel' => null,
            'nodeIdentifier' => null
        ],
        'app_tools' => [
            'toolName' => null
        ],
        'app_settings' => [],
        'app_users_list' => [],
        'app_users_editor' => [
            'userToken' => 'asdfasdfsdafdsafsdaf'
        ],
        'app_users_groups' => [],
        'app_lists' => [
            'listName' => 'categories'
        ]
    ];

    /**
     * Main page test
     */
    public function testStart()
    {
        foreach ($this->indexRoutes as $routeName => $routeParameters) {
            $this->client->request('GET', $this->router->generate($routeName, $routeParameters));
            $response = $this->client->getResponse();

            $this->assertTrue($response->isSuccessful());
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertContains('<app-root></app-root>', $response->getContent());
        }
    }

    public function testFavicon()
    {
        $this->client->request('GET', $this->router->generate('app_favicon'));

        $response = $this->client->getResponse();

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(FileSpecification::icon['mimeType'], $response->headers->get('Content-Type'));
    }
}