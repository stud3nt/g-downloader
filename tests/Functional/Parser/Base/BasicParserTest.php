<?php

namespace App\Tests\Functional\Parser\Base;

use App\Converter\ModelConverter;
use App\Manager\UserManager;
use App\Model\ParserRequest;
use App\Parser\Base\ParserInterface;
use App\Service\ParserService;
use App\Tests\Functional\Basic\BasicWebTestCase;
use App\Utils\TestsHelper;

class BasicParserTest extends BasicWebTestCase
{
    /** @var string|null */
    private $parserName = null;

    /** @var ParserInterface */
    protected $parser;

    /** @var ParserRequest */
    protected $parserRequest;

    protected function loadParser(string $parserName)
    {
        $container = self::bootKernel()->getContainer();

        $parserService = $container->get(ParserService::class);
        $userManager = $container->get(UserManager::class);

        $this->parser = $parserService->loadParser($parserName, $userManager->getByUsernameOrEmail('stud3nt'));
    }

    protected function prepareRequestModel()
    {
        $requestArray = TestsHelper::generateParserRequestArray();

        $this->parserRequest = new ParserRequest();

        $modelConverter = new ModelConverter();
        $modelConverter->setData($requestArray, $this->parserRequest);
    }

    public function testPrepareRequestModel()
    {
        $this->parserRequest = null;
        $this->prepareRequestModel();

        $this->assertNotNull($this->parserRequest);
        $this->assertIsString($this->parserRequest->getRequestIdentifier());
    }
}