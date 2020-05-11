<?php

namespace App\Tests\Functional\Converter;

use App\Converter\ModelConverter;
use App\Model\ParserRequest;
use App\Utils\StringHelper;
use App\Utils\TestsHelper;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ModelConverterTest extends WebTestCase
{
    public function testCreateParserRequestModelFromArray()
    {
        $actionName = StringHelper::randomStr(10);
        $apiToken = StringHelper::randomStr(32);

        $requestArray = TestsHelper::generateParserRequestArray();
        $requestArray['actionName'] = $actionName;
        $requestArray['apiToken'] = $apiToken;

        $parserRequest = new ParserRequest();
        $modelConverter = new ModelConverter();
        $modelConverter->setData($requestArray, $parserRequest);

        $this->assertEquals($actionName, $parserRequest->getActionName());
        $this->assertEquals($apiToken, $parserRequest->getApiToken());
    }
}