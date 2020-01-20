<?php

namespace App\Factory;

use App\Converter\ModelConverter;
use App\Factory\Base\RequestFactoryInterface;
use App\Model\ParsedNode;

class ParsedNodeFactory implements RequestFactoryInterface
{
    /**
     * @param array $requestData
     * @return ParsedNode
     * @throws \ReflectionException
     */
    public function buildFromRequestData(array $requestData = []): ParsedNode
    {
        $parsedNode = new ParsedNode();

        $modelConverter = new ModelConverter();
        $modelConverter->setData($requestData, $parsedNode);

        return $parsedNode;
    }
}