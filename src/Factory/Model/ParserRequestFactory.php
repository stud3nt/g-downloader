<?php

namespace App\Factory\Model;

use App\Annotation\Serializer\ObjectVariable;
use App\Converter\ModelConverter;
use App\Converter\ObjectSerializer;
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
        if (!is_array($requestData))
            $requestData = json_decode(json_encode($requestData), true);

        $objectVariable = new ObjectSerializer();
        $parserRequest = $objectVariable->deserialize($requestData, ParserRequest::class);

        return $parserRequest;
    }
}