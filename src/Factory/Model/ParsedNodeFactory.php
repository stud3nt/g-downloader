<?php

namespace App\Factory\Model;

use App\Factory\Base\BaseFactory;
use App\Factory\Base\RequestFactoryInterface;
use App\Model\ParsedNode;

class ParsedNodeFactory extends BaseFactory implements RequestFactoryInterface
{
    /**
     * @param array $requestData
     * @return ParsedNode
     * @throws \ReflectionException
     */
    public function buildFromRequestData(array $requestData = []): ParsedNode
    {
        return $this->objectSerializer->deserialize($requestData, ParsedNode::class);
    }
}