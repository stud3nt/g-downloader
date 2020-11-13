<?php

namespace App\Factory\Model;

use App\Factory\Base\BaseFactory;
use App\Factory\Base\RequestFactoryInterface;
use App\Model\ParserRequest;

class ParserRequestFactory extends BaseFactory implements RequestFactoryInterface
{
    /**
     * @param $requestData
     * @return ParserRequest
     * @throws \ReflectionException
     */
    public function buildFromRequestData(array $requestData = []): ParserRequest
    {
        return $this->objectSerializer->deserialize($requestData, ParserRequest::class);
    }
}