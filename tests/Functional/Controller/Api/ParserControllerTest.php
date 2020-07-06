<?php

namespace App\Tests\Functional\Controller\Api;

use App\Converter\EntityConverter;
use App\Enum\NodeLevel;
use App\Enum\ParserType;
use App\Factory\ParserRequestFactory;
use App\Tests\Functional\Controller\BasicControllerTestcase;
use App\Utils\TestsHelper;

class ParserControllerTest extends BasicControllerTestcase
{
    const CheckNodes = 'nodes';
    const CheckFiles = 'files';

    protected $parserRequestsData = [];

    protected $testedNodes = [
        ParserType::Boards4chan => [
            NodeLevel::BoardsList => self::CheckNodes,
            NodeLevel::Board => self::CheckNodes
        ],
        ParserType::Reddit => [
            NodeLevel::Board => self::CheckFiles
        ],
        ParserType::Imagefap => [
            NodeLevel::Owner => self::CheckNodes,
            NodeLevel::BoardsList => self::CheckNodes
        ]
    ];

    public function setUp()
    {
        parent::setUp();

        $this->prepareParsersRequestsData();
    }

    public function testParsingNode()
    {
        $this->executeAnonymousUserRequest($this->client, 'api_parsers_action','POST',true);
        $this->logoutUserFromClient($this->client);

        if ($this->parserRequestsData) {
            foreach ($this->parserRequestsData as $parserRequestData) {
                for ($iterator = 0; $iterator < 2; $iterator++) {
                    $parser = $parserRequestData['currentNode']['parser'];
                    $level = $parserRequestData['currentNode']['level'];

                    $parserRequestData['ignoreCache'] = $iterator == 1; // first without cache, then normally

                    $parserRequest = (new ParserRequestFactory())->buildFromRequestData($parserRequestData);
                    $parserRequest->getStatus()->end(); // ends errored requests;

                    $this->executeAdminUserRequest($this->client, 'api_parsers_action','POST',false, $parserRequestData);

                    $response = $this->clientResponseAssertions($this->client, false);
                    $json = $this->responseToObjectAssertions($response);

                    $this->assertEquals($parserRequestData['currentNode']['name'], $json->currentNode->name);

                    switch ($parserRequestData['checkedTypes']) {
                        case self::CheckNodes:
                            $this->assertNotEmpty($json->parsedNodes, 'Empty nodes on '.$parser.' / '.$level);
                            $this->assertIsArray($json->parsedNodes, 'Nodes aren\'t array on '.$parser.' / '.$level);
                            break;

                        case self::CheckFiles:
                            $this->assertNotEmpty($json->files, 'Empty files on '.$parser.' / '.$level);
                            $this->assertIsArray($json->files, 'Files aren\'t array on '.$parser.' / '.$level);
                            break;
                    }

                }
            }
        }
    }

    private function prepareParsersRequestsData(): void
    {
        $nodeManager = $this->loadNodeManager();
        $entityConverter = new EntityConverter();
        $entityConverter->setEntityManager($this->loadEntityManager());

        foreach ($this->testedNodes as $testedNodeParser => $testedNodeLevels) {
            foreach ($testedNodeLevels as $testedNodeLevel => $checkedTypes) {
                $randomNode = $nodeManager->getRandom(['parser' => $testedNodeParser, 'level' => $testedNodeLevel]);

                if (!$randomNode)
                    continue;

                $requestArray = TestsHelper::generateParserRequestArray();
                $requestArray['currentNode'] = $entityConverter->convert($randomNode);
                $requestArray['checkedTypes'] = $checkedTypes;

                $this->parserRequestsData[] = $requestArray;
            }
        }
    }
}