<?php

namespace App\Factory;

use App\Converter\ModelConverter;
use App\Factory\Base\RequestFactoryInterface;
use App\Model\ParserRequest;

class ParserRequestFactory implements RequestFactoryInterface
{
    /**
     * @param array $requestData
     * @return ParserRequest
     * @throws \ReflectionException
     */
    public function buildFromRequestData(array $requestData = []): ParserRequest
    {
        $parserRequest = new ParserRequest();

        $modelConverter = new ModelConverter();
        $modelConverter->setData($requestData, $parserRequest);

        // clear files and nodes sent from outside;
        $parserRequest->clearFiles()->clearParsedNodes();

        return $parserRequest;
    }
}