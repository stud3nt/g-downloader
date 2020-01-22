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

        return $parserRequest;
    }
}