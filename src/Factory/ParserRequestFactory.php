<?php

namespace App\Factory;

use App\Converter\ModelConverter;
use App\Factory\Base\RequestFactoryInterface;
use App\Model\ParserRequest;

class ParserRequestFactory implements RequestFactoryInterface
{
    /**
     * @param $requestData
     * @return ParserRequest
     * @throws \ReflectionException
     */
    public function buildFromRequestData($requestData = []): ParserRequest
    {
        $parserRequest = new ParserRequest();

        if (!is_array($requestData))
            $requestData = json_decode(json_encode($requestData), true);

        $modelConverter = new ModelConverter();
        $modelConverter->setData($requestData, $parserRequest, true);

        return $parserRequest;
    }
}