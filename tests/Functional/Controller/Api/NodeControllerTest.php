<?php

namespace App\Tests\Functional\Controller\Api;

use App\Converter\EntityConverter;
use App\Manager\Object\NodeManager;
use App\Tests\Functional\Controller\BasicControllerTestcase;
use App\Utils\StringHelper;

class NodeControllerTest extends BasicControllerTestcase
{
    public function testUpdateNode()
    {
        $this->executeAnonymousUserRequest($this->client, 'api_node_update', 'POST', true);

        $nodeManager = $this->containerInstance->get(NodeManager::class);
        $entityConverter = new EntityConverter();
        $entityConverter->setEntityManager($this->loadEntityManager());

        if ($nodeManager->getCount() > 0) {
            $latestNode = $nodeManager->getRandom();
            $latestNodeData = $entityConverter->convert($latestNode);
            $testDescription = "TEST_DESCR ".StringHelper::randomStr(32);
            $latestNodeDataBackup = $latestNodeData;
            $latestNodeData['description'] = $testDescription;

            $this->executeAdminUserRequest($this->client, 'api_node_update', 'POST', false, $latestNodeData);

            $response = $this->clientResponseAssertions($this->client, false);
            $json = $this->responseToObjectAssertions($response);

            $this->assertEquals($testDescription, $json->description);

            $changedNode = $nodeManager->getOneBy(['id' => $latestNode->getId()]);

            $this->assertEquals($testDescription, $changedNode->getDescription());

            // changes rollback
            $this->executeAdminUserRequest($this->client, 'api_node_update', 'POST', false, $latestNodeDataBackup);
        } else {
            $this->assertTrue(true);
        }
    }
}